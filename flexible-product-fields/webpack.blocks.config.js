// Webpack config specifically for WordPress blocks
// This allows wp-scripts to handle block building separately from our main webpack config

const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
	...defaultConfig,
	// Let wp-scripts handle everything for blocks
};