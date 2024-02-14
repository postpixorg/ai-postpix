<?php

namespace AIPSTX;

if (!defined('ABSPATH'))
	exit;
if (!class_exists('\\AIPSTX\\AIPSTX_metabox')) {
	class AIPSTX_metabox {

		private static $instance = null;

		public static function get_instance() {
			if (is_null(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}


		public function __construct() {


			add_action('add_meta_boxes', array($this, 'add_custom_meta_box'));
		}

		public function add_custom_meta_box() {
			add_meta_box(
				'aipstx_meta_box', // Meta box ID
				'AI Postpix', // Meta box başlığı
				array($this, 'aipstx_meta_box_html'), // Meta box içeriği için callback fonksiyon
				'post', // Gösterileceği ekran türü (burada 'post' olarak belirlendi)
				'normal', // Meta box'ın gösterileceği alan (normal, side, advanced)
				'high' // Öncelik seviyesi
			);
		}


		public function aipstx_meta_box_html($post) {
			$aipstx_edenai_key = get_option('aipstx_edenai_key');
			$aipstx_openai_key = get_option('aipstx_openai_key');


			// API anahtarlarının her ikisi de boşsa uyarı göster
			if (empty($aipstx_edenai_key) && empty($aipstx_openai_key)) {
				echo '<div style="background-color: #f8f3ff; width: 100%; height: 300px; position: absolute; top: 50%; left: 0; z-index: 1000; text-align: center;">
				<p style="color:#585656; font-size: 20px; position: relative; top: 40%;">Please enter any API key.</p>
				</div>';
				return; // Fonksiyonun geri kalanını çalıştırmamak için return kullan
			}

			?>
			<div id="postpix-alert" class="postpix-alert" style="display:none;"></div>
			<div class="top">
				<div class="left-group">
					<div class="pv_prompt_actions">
						<button id="aipstx_find_prompt" class="button"><i class="fas fa-magic"></i>&nbsp;&nbsp;Find My
							Prompt</button>
					</div> <div class="prompt-area">
						<div class="prompt-container">
							<label for="pv_prompt">Prompt for Images:</label>
							<div id="pv_loading" style="display: none;">
								<img src="<?php echo esc_url(plugins_url('img/loading.gif', dirname(__FILE__))); ?>"
									alt="Loading..." />
							</div>
						</div>
						<textarea id="pv_prompt" name="pv_prompt"
							placeholder="Type your own prompt or click on the 'Find My Prompt' button."></textarea>
					</div><button type="button" id="aipstx_create_image" class="button button-primary">
						<i class="fas fa-paint-brush"></i>&nbsp;&nbsp;Create Images
					</button>
				</div>
				<div class="right-group">
					<div class="options-container">
						<div class="option-group">
							<label for="engine">Generate Image with:</label>
							<select name="engine" id="engine">
								<option value="openai">DALL-E 2</option>
								<option value="dall-e3">DALL-E 3</option>
								<option value="deepai">Deep AI</option>
								<option value="stabilityai">Stable-Diffusion</option>
								<option value="replicate">Replicate</option>
							</select>
						</div>
						<div class="option-group">
							<div class="numberimages">
								<label for="postpix_image_count">Number of Images:</label>
								<select name="postpix_image_count" id="postpix_image_count">
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
								</select>
							</div>
						</div>
					</div>
					<div class="resolution-group">
						<label for="resolution">Resolution:</label>
						<select name="resolution" id="resolution">
							<option value="256x256">256x256</option>
							<option value="512x512">512x512</option>
							<option value="1024x1024" selected>1024x1024</option>
						</select>
					</div>
				</div>
			</div>

			<div id="prompt-modal" style="display:none;">
				<div id="modal-header">
					<span class="modal-title">Prompt Suggestions</span>
					<button class="close-modal">&times;</button> <!-- Kapatma butonu -->
				</div>
				<div id="prompt-suggestions">
					<!-- AJAX ile doldurulacak prompt önerileri burada olacak -->
				</div>
			</div>

			<div id="pv_images_container" style="margin: 20px;">
				<!-- Oluşturulan görseller burada gösterilecek -->
			</div>
			<?php
		}
	}

	aipstx_metabox::get_instance();
}