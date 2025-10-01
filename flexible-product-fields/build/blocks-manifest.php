<?php
// This file is generated. Do not modify it manually.
return array(
	'fpf-template-selector' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'fpf/template-selector',
		'version' => '0.1.0',
		'title' => 'Flexible Product Fields',
		'category' => 'widgets',
		'description' => 'Display a flexible product fields template',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'templateId' => array(
				'type' => 'string',
				'default' => ''
			),
			'showOtherFields' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'textdomain' => 'flexible-product-fields',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	)
);
