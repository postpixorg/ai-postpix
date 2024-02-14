<?php
if (!defined('ABSPATH'))
	exit;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://postpix.org
 * @since      1.0.0
 *
 * @package    Ai_Postpix
 * @subpackage Ai_Postpix/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ai_Postpix
 * @subpackage Ai_Postpix/admin
 * @author     Dogu Pekgoz <aipostpix@gmail.com>
 */
class AIPSTX_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action('admin_init', array($this, 'options_update'));
		add_action('admin_init', array($this, 'aipstx_save_settings_callback'));
		add_action('wp_ajax_aipstx_test_edenai', array($this, 'aipstx_test_edenai_ajax_handler'));
		add_action('wp_ajax_aipstx_test_openai', array($this, 'aipstx_test_openai_ajax_handler'));
		add_action('admin_head', array($this, 'aipstx_custom_admin_dark_mode'));
	}

	public function add_plugin_admin_menu() {
		add_menu_page(
			'AI Postpix',
			'AI Postpix',
			'manage_options',
			$this->plugin_name,
			array($this, 'aipstx_display_plugin_setup_page'),
			plugins_url('img/pxmini.png', dirname(__FILE__)),
			26
		);
	}


	function aipstx_custom_admin_dark_mode() {
		global $pagenow;

		// Eklentinizin sayfa tanımlayıcısını kontrol edin
		if ($pagenow == 'admin.php' && isset($_GET['page']) && sanitize_text_field($_GET['page']) == 'ai-postpix') {
			$theme_style = get_option('aipstx_theme_style', 'light');
			if ($theme_style == 'dark') {
				echo '<style>body { background: #0d0d0d; }</style>';
			}
		}
	}


	public function aipstx_save_settings_callback() {
		static $already_called = false;
		if ($already_called) {
			return;
		}
		$already_called = true;
		if (isset($_POST['postpix-settings-submit'])) {  // Form gönderme kontrolü
			// Nonce ve yetki kontrolleri
			if (!isset($_POST['aipstx_settings_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aipstx_settings_nonce'])), 'aipstx_settings_action') || !current_user_can('manage_options')) {
				add_settings_error('aipstx_settings', 'unauthorized', 'Unauthorized operation.', 'error');
			} else {
				// Seçenekleri güvenli bir şekilde kaydedin
				update_option('aipstx_edenai_key', sanitize_text_field($_POST['aipstx_edenai_key']));
				update_option('aipstx_openai_key', sanitize_text_field($_POST['aipstx_openai_key']));
				update_option('aipstx_prompt_engine', sanitize_text_field($_POST['aipstx_prompt_engine']));
				update_option('aipstx_theme_style', sanitize_text_field($_POST['aipstx_theme_style']));
				add_settings_error('aipstx_settings', 'settings_updated', 'Settings saved successfully.', 'updated');
			}
		}
	}



	function aipstx_test_edenai_ajax_handler() {
		check_ajax_referer('aipstx_settings_action', 'nonce');

		$aipstx_edenai_key = isset($_POST['test_api']) ? sanitize_text_field($_POST['test_api']) : '';
		$plugin_name = 'ai-postpix';  // Eklentinin adı
		$version = '1.0.0';  // Eklentinin sürümü
		$test_result = (new aipstx_Admin($plugin_name, $version))->aipstx_test_edenai($aipstx_edenai_key);

		if ($test_result) {
			wp_send_json_success('Eden AI API test successful.');
		} else {
			wp_send_json_error('Eden AI API test failed.');
		}
	}

	function aipstx_test_openai_ajax_handler() {
		check_ajax_referer('aipstx_settings_action', 'nonce');

		$aipstx_openai_key = isset($_POST['aipstx_test_openai']) ? sanitize_text_field($_POST['aipstx_test_openai']) : '';
		$plugin_name = 'ai-postpix';  // Eklentinin adı
		$version = '1.0.0';  // Eklentinin sürümü
		$test_result = (new aipstx_Admin($plugin_name, $version))->aipstx_test_openai($aipstx_openai_key);

		if ($test_result) {
			wp_send_json_success('OpenAI API test successful.');
		} else {
			wp_send_json_error('OpenAI API test failed.');
		}
	}


	private function aipstx_test_edenai($aipstx_edenai_key) {
		$url = 'https://api.edenai.run/v2/text/chat';
		$body = wp_json_encode([
			"providers" => "google",
			"text" => "Hi",
			"response_as_dict" => true,
			"attributes_as_list" => false,
			"show_original_response" => false,
			"temperature" => 0,
			"max_tokens" => 1000
		]);
		$args = array(
			'method' => 'POST',
			'timeout' => '60',
			'headers' => array(
				'Accept' => 'application/json',
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $aipstx_edenai_key
			),
			'body' => $body,
			'data_format' => 'body'
		);

		$response = wp_remote_post($url, $args);

		if (is_wp_error($response)) {
			// WP_Error durumunda hata mesajını ve false döndür
			$error_message = $response->get_error_message();
			echo "Something went wrong: " . esc_html($error_message);
			return false;
		} else {
			// Burada API'nin başarılı bir yanıt verip vermediğini kontrol edin
			$response_data = json_decode(wp_remote_retrieve_body($response), true);
			if (isset($response_data['google']['status']) && $response_data['google']['status'] == 'success') {
				return true;
			} else {
				return false;
			}
		}
	}


	function aipstx_test_openai($aipstx_openai_key) {
		$url = 'https://api.openai.com/v1/engines';

		$args = array(
			'timeout' => '60',
			'headers' => array(
				'Authorization' => 'Bearer ' . $aipstx_openai_key
			)
		);

		$response = wp_remote_get($url, $args);

		if (is_wp_error($response)) {
			// WP_Error durumunda, false döndür
			return false;
		}

		// API yanıtını JSON olarak ayrıştır
		$response_data = json_decode(wp_remote_retrieve_body($response), true);

		// Yanıtta 'data' anahtarı varsa ve bu bir dizi ise, API anahtarı geçerlidir
		if (isset($response_data['data']) && is_array($response_data['data'])) {
			return true;
		}

		// Diğer durumlarda, API anahtarı geçersizdir
		return false;
	}


	public function aipstx_display_plugin_setup_page() {
		include_once('partials/ai-postpix-admin-display.php');
	}

	public function options_update() {
		register_setting('postpix', 'aipstx_edenai_key');
		register_setting('postpix', 'aipstx_openai_key');
		register_setting('postpix', 'aipstx_prompt_engine');
		register_setting('postpix', 'aipstx_theme_style');
	}


	public function validate($input) {
		// Burada giriş alanlarının doğrulama kodunu ekleyebilirsiniz
		return $input;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in aipstx_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The aipstx_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ai-postpix-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in aipstx_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The aipstx_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ai-postpix-admin.js', array('jquery'), filemtime(plugin_dir_path(__FILE__) . 'js/ai-postpix-admin.js'), true);
	}
}
