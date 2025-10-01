<?php
/**
 * Plugin.
 *
 * @package Flexible Product Fields
 */

use WPDesk\FPF\Free\Plugin as PluginFree;
use VendorFPF\WPDesk\View\Renderer\Renderer;
use VendorFPF\WPDesk\View\Resolver\DirResolver;
use VendorFPF\WPDesk\View\Renderer\SimplePhpRenderer;
use WPDesk\FPF\Free\Marketing\SupportPage;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateFinder;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateQuery;
use WPDesk\FPF\Free\Block\Settings\BlockTemplateSettings;

/**
 * Plugin.
 */
class Flexible_Product_Fields_Plugin extends VendorFPF\WPDesk\PluginBuilder\Plugin\AbstractPlugin implements VendorFPF\WPDesk\PluginBuilder\Plugin\HookableCollection {

	use VendorFPF\WPDesk\PluginBuilder\Plugin\HookableParent;
	use VendorFPF\WPDesk\PluginBuilder\Plugin\TemplateLoad;

	/**
	 * Scripts version string.
	 *
	 * @var string
	 */
	private $scripts_version = FLEXIBLE_PRODUCT_FIELDS_VERSION . '.69';

	/**
	 * FPF product fields.
	 *
	 * @var FPF_Product_Fields
	 */
	private $fpf_product_fields;

	/**
	 * FPF Product.
	 *
	 * @var FPF_Product
	 */
	private $fpf_product;

	/**
	 * FPF Product Price.
	 *
	 * @var FPF_Product_Price
	 */
	private $fpf_product_price;

	/**
	 * FPF Cart.
	 *
	 * @var FPF_Cart
	 */
	private $fpf_cart;

	/**
	 * FPF post type.
	 *
	 * @var FPF_Post_Type
	 */
	private $fpf_post_type;

	/**
	 * Instance of new version main class of plugin.
	 *
	 * @var PluginFree
	 */
	private $plugin_free;

	/**
	 * @var Renderer
	 */
	private $renderer;

	private TemplateFinder $template_finder;

	/**
	 * Flexible_Invoices_Reports_Plugin constructor.
	 *
	 * @param VendorFPF\WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( VendorFPF\WPDesk_Plugin_Info $plugin_info ) {
		$this->plugin_info = $plugin_info;
		parent::__construct( $this->plugin_info );
		$this->renderer    = new SimplePhpRenderer( new DirResolver( dirname( __DIR__ ) . '/templates' ) );
		$this->plugin_free = new PluginFree( $plugin_info, $this, $this->renderer );
	}

	/**
	 * Init base variables for plugin
	 */
	public function init_base_variables() {
		$this->plugin_url       = $this->plugin_info->get_plugin_url();
		$this->plugin_path      = $this->plugin_info->get_plugin_dir();
		$this->template_path    = $this->plugin_info->get_text_domain();
		$this->plugin_namespace = $this->plugin_info->get_text_domain();
		$this->template_path    = $this->plugin_info->get_text_domain();
	}

	/**
	 * Enqueue front scripts.
	 */
	public function wp_enqueue_scripts() {
		if ( ! is_singular( 'product' ) ) {
			return;
		}

		$product = wc_get_product( get_the_ID() );
		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		$block_settings = $this->get_block_template_settings();

		$this->enqueue_scripts( $product, $block_settings );
	}


	public function enqueue_scripts( \WC_Product $product, ?BlockTemplateSettings $block_settings = null ): void {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'fpf_front', trailingslashit( $this->get_plugin_assets_url() ) . 'css/front' . $suffix . '.css', [], $this->scripts_version );
		wp_enqueue_script( 'fpf_product', trailingslashit( $this->get_plugin_assets_url() ) . 'js/fpf_product' . $suffix . '.js', [ 'jquery' ], $this->scripts_version );

		wp_enqueue_style( 'fpf_new_front', trailingslashit( $this->get_plugin_assets_url() ) . 'css/new-front.css', [], $this->scripts_version );
		wp_enqueue_script( 'fpf_new_front', trailingslashit( $this->get_plugin_assets_url() ) . 'js/new-front.js', [], $this->scripts_version, true );

		wp_localize_script(
			'fpf_product',
			'fpf_product',
			[
				'total'                        => __( 'Total', 'flexible-product-fields' ),
				'currency_format_num_decimals' => absint( get_option( 'woocommerce_price_num_decimals' ) ),
				'currency_format_symbol'       => get_woocommerce_currency_symbol(),
				'currency_format_decimal_sep'  => esc_attr( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ) ),
				'currency_format_thousand_sep' => esc_attr( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ) ),
				'currency_format'              => $this->get_currency_format(),
				'fields_rules'                 => $this->fpf_product->get_logic_rules_for_product( $product, $block_settings ),
				'fpf_fields'                   => $this->fpf_product->get_fields_data( $product, $block_settings ),
				'fpf_product_price'            => $this->fpf_product->get_product_price_data( $product ),
			]
		);
	}

	private function get_currency_format(): string {
		if ( ! function_exists( 'get_woocommerce_price_format' ) ) {
			$currency_pos = get_option( 'woocommerce_currency_pos' );
			switch ( $currency_pos ) {
				case 'left':
					$format = '%1$s%2$s';
					break;
				case 'right':
					$format = '%2$s%1$s';
					break;
				case 'left_space':
					$format = '%1$s&nbsp;%2$s';
					break;
				case 'right_space':
					$format = '%2$s&nbsp;%1$s';
					break;
			}

			return esc_attr( str_replace( [ '%1$s', '%2$s' ], [ '%s', '%v' ], $format ) );
		}

		return esc_attr(
			str_replace(
				[ '%1$s', '%2$s' ],
				[
					'%s',
					'%v',
				],
				get_woocommerce_price_format()
			)
		);
	}

	/**
	 * Check if a block exists in the current template
	 */
	public function has_block( string $block_name ): bool {
		global $_wp_current_template_content;

		return has_block( $block_name, $_wp_current_template_content );
	}

	/**
	 * Recursively search for a block by name and return its attributes.
	 *
	 * @param array<array<string, mixed>> $blocks
	 * @param string $block_name
	 *
	 * @return array<string, mixed>|null
	 */
	private function find_block_attrs_recursive( array $blocks, string $block_name ): ?array {
		foreach ( $blocks as $block ) {
			if ( isset( $block['blockName'] ) && $block_name === $block['blockName'] ) {
				return $block['attrs'] ?? [];
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$attrs = $this->find_block_attrs_recursive( $block['innerBlocks'], $block_name );
				if ( null !== $attrs ) {
					return $attrs;
				}
			}
		}

		return null;
	}

	/**
	 * Get attributes of a block by its name from the current template.
	 *
	 * @param string $block_name
	 * @return array<string, mixed>|null
	 */
	private function get_block_attrs( string $block_name ): ?array {
		global $_wp_current_template_content;

		$blocks = parse_blocks( $_wp_current_template_content );
		return $this->find_block_attrs_recursive( $blocks, $block_name );
	}

	private function get_block_template_settings(): ?BlockTemplateSettings {
		if ( ! $this->has_block( 'fpf/template-selector' ) ) {
			return null;
		}

		$attrs = $this->get_block_attrs( 'fpf/template-selector' );
		return new BlockTemplateSettings(
			$attrs['templateId'] ?? 0,
			$attrs['showOtherFields'] ?? true,
		);
	}

	/**
	 * Load dependencies.
	 */
	private function load_dependencies() {
		require_once $this->plugin_path . '/inc/wpdesk-woo27-functions.php';

		new WPDesk_Flexible_Product_Fields_Tracker();
		$this->fpf_product_price  = new FPF_Product_Price();
		$this->fpf_product_fields = new FPF_Product_Fields( $this );
		$this->template_finder    = new TemplateFinder(
			new TemplateQuery(),
			$this->fpf_product_fields
		);
		$this->fpf_product        = new FPF_Product( $this, $this->fpf_product_fields, $this->fpf_product_price );
		$this->fpf_cart           = new FPF_Cart( $this, $this->fpf_product_fields, $this->fpf_product, $this->fpf_product_price );
		$this->fpf_post_type      = new FPF_Post_Type( $this, $this->fpf_product_fields, $this->renderer );
		$this->add_hookable( new FPF_Add_To_Cart_Filters( $this->fpf_product ) );

		new FPF_Order( $this );
	}

	/**
	 * Init.
	 */
	public function init() {
		$this->init_base_variables();
		$this->load_dependencies();
		$this->plugin_free->init();
		$this->hooks();
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		$this->plugin_free->hooks();
		parent::hooks();
		add_action( 'init', [ $this, 'init_polylang' ] );
		add_action( 'admin_init', [ $this, 'init_wpml' ] );
		$this->hooks_on_hookable_objects();
	}

	/**
	 * Getter for FPF_Product
	 */
	public function get_fpf_product(): FPF_Product {
		return $this->fpf_product;
	}

	public function get_template_finder(): TemplateFinder {
		return $this->template_finder;
	}

	/**
	 * Init Polylang actions.
	 */
	public function init_polylang() {
		if ( function_exists( 'pll_register_string' ) ) {
			$this->fpf_product_fields->init_polylang();
		}
	}

	/**
	 * Init WPML actions.
	 */
	public function init_wpml() {
		if ( function_exists( 'icl_register_string' ) ) {
			$this->fpf_product_fields->init_wpml();
		}
	}

	/**
	 * Add links to plugin on plugins page.
	 *
	 * @param array $links Links array.
	 *
	 * @return array
	 */
	public function links_filter( $links ) {
		$plugin_links = [
			'<a href="' . admin_url( 'edit.php?post_type=fpf_fields' ) . '">' . __( 'Settings', 'flexible-product-fields' ) . '</a>',
			'<a href="' . esc_url( apply_filters( 'flexible_product_fields/short_url', '#', 'fpf-settings-row-action-docs' ) ) . '" target="_blank">' . __( 'Docs', 'flexible-product-fields' ) . '</a>',
		];

		if ( ! wpdesk_is_plugin_active( 'flexible-product-fields-pro/flexible-product-fields-pro.php' ) ) {
			$plugin_links[] = '<a href="' . esc_url( apply_filters( 'flexible_product_fields/short_url', '#', 'fpf-settings-row-action-upgrade' ) ) . '" target="_blank" style="color:#FF9743;font-weight:bold;">' . __( 'Upgrade to PRO &rarr;', 'flexible-product-fields' ) . '</a>';
			$start_here     = '<a href="' . admin_url( 'admin.php?page=' . SupportPage::PAGE_SLUG ) . '" style="font-weight: bold;color: #007050">' . esc_html__( 'Start here', 'flexible-product-fields' ) . '</a>';
			array_unshift( $plugin_links, $start_here );
		}

		$plugin_links[] = $links['deactivate'];

		return $plugin_links;
	}
}
