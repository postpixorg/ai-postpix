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
 * @since             1.0.1
 * @package           Ai_Postpix
 *
 * @wordpress-plugin
 * Plugin Name:       AI Image Generator for Your Content & Featured Images - AI Postpix
 * Description:       Let AI analyze your content and generate images that match your content! DALL-E, Stable-Diffusion, and more. No more searching for the best image for your content, generate images with AI Postpix!
 * Version:           1.0.1
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
define( 'AIPSTX_VERSION', '1.0.1' );

if ( function_exists( 'aipstx_fs' ) ) {
    aipstx_fs()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    
    if ( !function_exists( 'aipstx_fs' ) ) {
        // Create a helper function for easy SDK access.
        function aipstx_fs()
        {
            global  $aipstx_fs ;
            
            if ( !isset( $aipstx_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $aipstx_fs = fs_dynamic_init( array(
                    'id'             => '14542',
                    'slug'           => 'ai-postpix',
                    'premium_slug'   => 'ai-postpix-pro',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_1ea9a174793392d56c99138272146',
                    'is_premium'     => false,
                    'premium_suffix' => 'Pro',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                    'slug'    => 'ai-postpix',
                    'support' => true,
                ),
                    'is_live'        => true,
                ) );
            }
            
            return $aipstx_fs;
        }
        
        // Init Freemius.
        aipstx_fs();
        // Signal that SDK was initiated.
        do_action( 'aipstx_fs_loaded' );
    }
    
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
     * @since    1.0.1
     */
    function aipstx_run()
    {
        $plugin = new aipstx();
        $plugin->run();
    }
    
    aipstx_run();
}

require_once __DIR__ . '/ai-postpix-extra.php';