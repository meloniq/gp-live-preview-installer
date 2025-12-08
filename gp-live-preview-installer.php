<?php
/**
 * Plugin Name:       GP Live Preview Installer
 * Plugin URI:        https://blog.meloniq.net/gp-live-preview-installer/
 *
 * Description:       A GlotPress plugin that installs and configures the basic setup for Live Preview functionality.
 * Tags:              glotpress, live preview, installer
 *
 * Requires at least: 4.9
 * Requires PHP:      7.4
 * Version:           1.0
 *
 * Author:            MELONIQ.NET
 * Author URI:        https://meloniq.net/
 *
 * License:           GPLv2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain:       gp-live-preview-installer
 *
 * Requires Plugins:  glotpress
 *
 * @package Meloniq\GpLivePreviewInstaller
 */

namespace Meloniq\GpLivePreviewInstaller;

// If this file is accessed directly, then abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'GPLPI_TD', 'gp-live-preview-installer' );


/**
 * GP Init Setup.
 *
 * @return void
 */
function gp_init() {
	global $gplpi_setup;

	require_once __DIR__ . '/src/class-install.php';

	$gplpi_setup['install'] = new Install();
}
add_action( 'gp_init', 'Meloniq\GpLivePreviewInstaller\gp_init' );
