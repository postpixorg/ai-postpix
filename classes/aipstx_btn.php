<?php

namespace AIPSTX;

if (!defined('ABSPATH'))
	exit;
if (!class_exists('\\AIPSTX\\AIPSTX_btn')) {
	class AIPSTX_btn {

		private static $instance = null;

		public static function get_instance() {
			if (is_null(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}


		public function __construct() {


			add_action('wp_ajax_aipstx_add_media_library', array($this, 'aipstx_add_media_library'));
			add_action('wp_ajax_aipstx_ensure_image_and_set_featured', array($this, 'aipstx_ensure_image_and_set_featured'));
			add_action('wp_ajax_aipstx_add_post_content', array($this, 'aipstx_add_post_content'));
		}

		public function aipstx_add_media_library() {
			// Nonce kontrolü
			if (!check_ajax_referer('aipstx_nonce', 'nonce', false)) {
				wp_send_json_error(array('message' => 'Nonce verification failed.'));
				return;
			}

			// AJAX isteğinden gelen görsel URL'sini ve anahtar kelimeyi al
			$image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';
			$prompt = isset($_POST['prompt']) ? sanitize_text_field($_POST['prompt']) : '';

			// Görselin geçerli bir URL'ye sahip olduğundan ve anahtar kelimenin boş olmadığından emin ol
			if (empty($image_url)) {
				wp_send_json_error(array('message' => 'Invalid image URL or prompt.'));
				return;
			}

			// Görseli indirme işlemi
			$response = wp_remote_get($image_url);
			if (is_wp_error($response)) {
				wp_send_json_error(array('message' => 'Error downloading image.'));
				return;
			}

			$image_data = wp_remote_retrieve_body($response);
			if (empty($image_data)) {
				wp_send_json_error(array('message' => 'Error downloading image.'));
				return;
			}

			// Görselin hash değerini al ve bu değeri kullanarak dosya adını oluştur
			$image_hash = md5($image_data);
			$file_name = 'image-' . $image_hash . '.png';

			// Görselin zaten indirilip indirilmediğini kontrol et
			global $wpdb;

			// Doğrudan $wpdb->get_var() içinde $wpdb->prepare() kullanımı
			$attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'attachment'", $file_name));

			if ($attachment_id) {
				// Görsel zaten indirilmiş, mevcut ID'yi döndür
				wp_send_json_success(array('message' => 'Image already exists in the media library.', 'attachment_id' => $attachment_id));
				return;
			}

			// İçeriği geçici bir dosyaya yaz
			$upload = wp_upload_bits($file_name, null, $image_data);
			if ($upload['error']) {
				wp_send_json_error(array('message' => 'File write error: ' . $upload['error']));
				return;
			}

			// Dosyayı medya kütüphanesine ekle
			$file_path = $upload['file'];
			$file_url = $upload['url'];
			$file_type = 'image/png';

			// Eklenecek dosya için bir attachment array'i oluştur
			$attachment = array(
				'guid' => $file_url,
				'post_mime_type' => $file_type,
				'post_title' => $file_name,
				'post_content' => '',
				'post_status' => 'inherit'
			);

			// Dosyayı attachment olarak ekleyin
			$attach_id = wp_insert_attachment($attachment, $file_path);
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
			wp_update_attachment_metadata($attach_id, $attach_data);

			// Başarı durumunda, görselin ID'sini geri gönder
			if ($attach_id) {
				wp_send_json_success(array('attachment_id' => $attach_id));
			} else {
				wp_send_json_error(array('message' => 'Error creating attachment.'));
			}
		}


		public function aipstx_ensure_image_and_set_featured() {
			// Nonce kontrolü
			if (!check_ajax_referer('aipstx_nonce', 'nonce', false)) {
				wp_send_json_error(array('message' => 'Nonce verification failed.'));
				return;
			}

			if (!isset($_POST['post_id']) || !isset($_POST['image_id'])) {
				wp_send_json_error(array('message' => "Invalid post ID or image ID."));
				return;
			}
			$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
			$attachment_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;

			$result = set_post_thumbnail($post_id, $attachment_id);
			if ($result) {
				// Yeni öne çıkan görselin URL'sini al
				$featured_image_url = wp_get_attachment_url($attachment_id);

				// Başarılı yanıtı ve görsel URL'sini gönder
				wp_send_json_success(array('message' => 'Featured image set with new library image.', 'featured_image_url' => $featured_image_url));
			} else {
				$error_string = 'Failed to set featured image for post ID ' . $post_id . ' after creating new attachment ID ' . $attachment_id;
				wp_send_json_error(array('message' => $error_string));
			}
		}

		public function aipstx_add_post_content() {
			if (!check_ajax_referer('aipstx_nonce', 'nonce', false)) {
				wp_send_json_error(array('message' => 'Nonce verification failed.'));
				return;
			}

			$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
			$media_id = isset($_POST['media_id']) ? intval($_POST['media_id']) : 0;

			if (!$post_id || !$media_id) {
				wp_send_json_error('Invalid post ID or media ID.');
				return;
			}

			// Yazının başlığını al
			$post_title = get_the_title($post_id);
			if (!$post_title) {
				wp_send_json_error('Unable to retrieve post title.');
				return;
			}

			// Get the media URL
			$media_url = wp_get_attachment_url($media_id);
			if (!$media_url) {
				wp_send_json_error('Invalid media ID.');
				return;
			}

			// Get the current post content
			$post_content = get_post_field('post_content', $post_id);

			// Create the image HTML
			$image_html = '<img src="' . esc_url($media_url) . '" alt="' . esc_attr($post_title) . '"/>';

			// Append the image HTML to the post content
			$new_post_content = $post_content . $image_html;

			// Update the post with the new content
			wp_update_post(array(
				'ID' => $post_id,
				'post_content' => $new_post_content
			));

			// Return the image HTML so it can be added to the post content in the editor without a page reload
			wp_send_json_success(array('image_html' => $image_html));
		}
	}

	aipstx_btn::get_instance();
}
