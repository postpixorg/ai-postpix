<?php

namespace AIPSTX;

if (!defined('ABSPATH'))
	exit;
if (!class_exists('\\AIPSTX\\AIPSTX_hook')) {
	class AIPSTX_hook {

		private static $instance = null;

		public static function get_instance() {
			if (is_null(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}


		public function __construct() {
			// Sadece admin paneli için stil ve script dosyalarını yükle
			add_action('admin_enqueue_scripts', [$this, 'aipstx_enqueue_styles']);
			add_action('admin_enqueue_scripts', [$this, 'aipstx_enqueue_scripts']);
		}

		public function aipstx_enqueue_styles() {
			$theme_style = get_option('aipstx_theme_style', 'light');

			// Public için CSS dosyası yolu ve sürümü
			$public_css_file_name = $theme_style === 'dark' ? 'ai-postpix-public-dark.css' : 'ai-postpix-public.css';
			$public_css_path = plugin_dir_path(__DIR__) . 'public/css/' . $public_css_file_name;
			$public_css_ver = filemtime($public_css_path);

			// Admin için CSS dosyası yolu ve sürümü
			$admin_css_file_name = $theme_style === 'dark' ? 'ai-postpix-admin-dark.css' : 'ai-postpix-admin.css';
			$admin_css_path = plugin_dir_path(__DIR__) . 'admin/css/' . $admin_css_file_name;
			$admin_css_ver = filemtime($admin_css_path);

			// Public ve Admin CSS dosyalarını yükle
			wp_enqueue_style('ai-postpix-public', plugin_dir_url(__DIR__) . 'public/css/' . $public_css_file_name, array(), $public_css_ver, 'all');
			wp_enqueue_style('ai-postpix-admin', plugin_dir_url(__DIR__) . 'admin/css/' . $admin_css_file_name, array(), $admin_css_ver, 'all');

			// FontAwesome yükle
			wp_enqueue_style('aipstx_fontawesome', plugin_dir_url(__DIR__) . 'public/css/all.min.css', array(), '5.15.3');
		}

		public function aipstx_enqueue_scripts() {
			// Public ve Admin için JavaScript dosyalarını yükle
			wp_enqueue_script('ai-postpix-public-js', plugin_dir_url(__DIR__) . 'public/js/ai-postpix-public.js', array('jquery'), filemtime(plugin_dir_path(__DIR__) . 'public/js/ai-postpix-public.js'), true);
			wp_enqueue_script('ai-postpix-admin-js', plugin_dir_url(__DIR__) . 'admin/js/ai-postpix-admin.js', array('jquery'), filemtime(plugin_dir_path(__DIR__) . 'admin/js/ai-postpix-admin.js'), true);

			// JavaScript lokalizasyonu
			aipstx_AjaxHandler::localizeScripts('ai-postpix-public-js');
			aipstx_AjaxHandler::localizeScripts('ai-postpix-admin-js');
			wp_localize_script('ai-postpix-admin-js', 'aipstxParams', array('loadingGifUrl' => plugins_url('/img/loading-big.gif', dirname(__FILE__))));
		}

	}

	aipstx_hook::get_instance();
}