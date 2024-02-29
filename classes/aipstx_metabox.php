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
			$aipstx_img_styles = class_exists('\\AIPSTX\\AIPSTX_img_styles') ? aipstx_img_styles::get_instance()->aipstx_get_available_styles() : [];

			?>
			<div id="postpix-alert" class="postpix-alert" style="display:none;"></div>
			<div class="top">
				<div class="left-group">
					<div class="pv_prompt_actions">
						<button id="aipstx_find_prompt" class="button"><i class="fas fa-magic"></i>&nbsp;&nbsp;Find My
							Prompt</button>
						<?php
						$is_aipstx_imp_available = class_exists('\\AIPSTX\\AIPSTX_improve');
						?>
						<button id="improve-prompt-button" class="button" <?php if (!$is_aipstx_imp_available)
							echo 'disabled'; ?>>
							<i class="fas fa-hat-wizard"></i>&nbsp;&nbsp;Improve My Prompt
						</button>
					</div><?php
					if (!$is_aipstx_imp_available) {
						if (aipstx_fs()->is_not_paying()) {
							echo '<a href="' . esc_url(aipstx_fs()->get_upgrade_url()) . '" class="upgrade-link">' . esc_html__('Upgrade Now!', 'ai-postpix') . '</a>';
						}
					}
					?> <div class="prompt-area">
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
					<div class="style-options-container">
						<div class="style-options-header">
							<p>Image Styles:</p>
						</div>
						<div class="style-options" <?php if (empty($aipstx_img_styles))
							echo 'style="position: relative;"'; ?>>
							<?php
							if (!empty($aipstx_img_styles)) {
								// Premium içerik
								$i = 0;
								foreach ($aipstx_img_styles as $aipstx_style_key => $aipstx_style_label) {
									// Her 8 stil için yeni bir style-column başlat
									if ($i % 8 == 0) {
										if ($i != 0) {
											echo '</div>'; // Önceki style-column'ı kapat
										}
										echo '<div class="style-column">'; // Yeni bir style-column başlat
									}
									$label_text = ucwords(str_replace(['_', 'style'], ' ', $aipstx_style_key));
									?>
									<div class="checkbox-group">
										<input type="checkbox" id="<?php echo esc_attr($aipstx_style_key); ?>" name="style[]"
											value="<?php echo esc_attr($aipstx_style_label); ?>">
										<label
											for="<?php echo esc_attr($aipstx_style_key); ?>"><?php echo esc_html(trim($label_text)); ?></label>
									</div>
									<?php
									$i++;
									if ($i == count($aipstx_img_styles)) {
										echo '</div>'; // Son style-column'ı kapat
									}
								}
							} else {
								// Ücretsiz içerik
								for ($i = 0; $i < 40; $i++) {
									if ($i % 8 == 0) {
										echo '<div class="style-column">';
									}
									?>
									<div class="checkbox-group">
										<input type="checkbox" id="style_<?php echo esc_attr($i); ?>" name="style[]" value="upgrade_pro"
											disabled>
										<label for="style_<?php echo esc_attr($i); ?>">Upgrade Pro</label>
									</div>
									<?php
									if (($i + 1) % 8 == 0 || $i == 7) {
										echo '</div>'; // Her 8 stil sonrası veya son stil için style-column'ı kapat
									}
								}
								?>
								<div class="upgrade-area">
									<?php
									if (aipstx_fs()->is_not_paying()) {
										echo '<a href="' . esc_url(aipstx_fs()->get_upgrade_url()) . '" class="upgrade-link-big">' . esc_html__('Upgrade Now!', 'ai-postpix') . '</a>';
									}
									?>

								</div>
								<?php
							}
							?>
						</div>
						<p> For the styles to work correctly, you must delete the style statements in your prompt (e.g.: a highly
							detailed and realistic digital painting) </p>
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