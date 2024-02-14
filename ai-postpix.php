<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://postpix.org
 * @since             1.0.0
 * @package           Ai_Postpix
 *
 * @wordpress-plugin
 * Plugin Name:       AI Postpix: Let AI Find the Best Image Prompts for Your Posts and Create Images!
 * Description:       Analyze your post with AI technology to create the most compatible images, and generate perfect images using your choice of various AI models including DALL-E2, DALL-E3, DeepAI, Stable-Diffusion, and Replicate. Don't search for the best image for your content, generate it!
 * Version:           1.0.0
 * Author:            Dogu Pekgoz
 * Author URI:        https://postpix.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ai-postpix
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AIPSTX_VERSION', '1.0.0' );

    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-ai-postpix-activator.php
     */
    function aipstx_activate()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-ai-postpix-activator.php';
        aipstx_Activator::activate();
    }
    
    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-ai-postpix-deactivator.php
     */
    function aipstx_deactivate()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-ai-postpix-deactivator.php';
        aipstx_Deactivator::deactivate();
    }
    
    register_activation_hook( __FILE__, 'aipstx_activate' );
    register_deactivation_hook( __FILE__, 'aipstx_deactivate' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-ai-postpix.php';
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function aipstx_run()
    {
        $plugin = new aipstx();
        $plugin->run();
    }
    
    aipstx_run();

require_once __DIR__ . '/ai-postpix-extra.php';