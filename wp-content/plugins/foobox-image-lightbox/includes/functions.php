<?php
/**
 * Common Foobox functions
 * Date: 27/08/2016
 */

function foobox_asset_url( $asset_relative_url ) {
	return trailingslashit(FOOBOX_BASE_URL) . 'assets/' . ltrim( $asset_relative_url, '/' );
}

function foobox_settings_url() {
	return admin_url( 'admin.php?page=' . FOOBOX_BASE_PAGE_SLUG_SETTINGS );
}

function foobox_pricing_url() {
	return admin_url( 'admin.php?page=foobox-image-lightbox-pricing' );
}

function foobox_freetrial_url() {
	return foobox_pricing_url() . '&trial=true';
}

function foobox_hide_pricing_menu() {
	return false;
}

/**
 * Filter out JavaScript-related keywords and inline scripts from an input string
 *
 * @param string $input
 * @return string
 */
function foobox_sanitize_javascript( $input ) {
	// List of JavaScript-related attributes to filter out.
	$javascript_attributes = array(
		'innerHTML',
		'document\.write',
		'eval',
		'Function\(',
		'setTimeout',
		'setInterval',
		'new Function\(',
		'onmouseover',
		'onmouseout',
		'onclick',
		'onload',
		'onchange',
		'<script>',
		'<\/script>',
		'encodeURIComponent',
		'decodeURIComponent',
		'JSON\.parse',
		'outerHTML',
		'innerHTML',
		'XMLHttpRequest',
		'createElement',
		'appendChild',
		'RegExp',
		'String\.fromCharCode',
		'encodeURI',
		'decodeURI',
		'javascript:',
	);

	$pattern = '/' . implode( '|', $javascript_attributes ) . '/i';

	// Use regex to replace potentially dangerous strings with an empty string.
	$input = preg_replace( $pattern, '', $input );

	return $input;
}
