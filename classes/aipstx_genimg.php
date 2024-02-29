<?php

namespace AIPSTX;

if (!defined('ABSPATH'))
    exit;
if (!class_exists('\\AIPSTX\\AIPSTX_genimg')) {
    class AIPSTX_genimg {

        private static $instance = null;

        public static function get_instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }


        public function __construct() {


            add_action('wp_ajax_aipstx_create_image', array($this, 'aipstx_create_image'));
        }

        public function aipstx_create_image() {
            if (!check_ajax_referer('aipstx_nonce', 'nonce', false)) {
                wp_send_json_error(array('message' => 'Nonce verification failed.'));
                return;
            }

            if (!current_user_can('edit_posts')) {
                wp_send_json_error('Unauthorized user');
                wp_die();
            }

            $prompt = isset($_POST['prompt']) ? sanitize_text_field($_POST['prompt']) : '';

            if (empty($prompt)) {
                wp_send_json_error('Prompt cannot be empty. Please type a prompt in the Prompt for Images field.');
                wp_die();
            }

            if (class_exists('\\AIPSTX\\AIPSTX_img_styles')) {
                $aipstx_styles = aipstx_img_styles::get_instance()->aipstx_get_styles_from_post();
                $aipstx_style_text = aipstx_img_styles::get_instance()->aipstx_process_styles($aipstx_styles);

                if (!empty($aipstx_styles)) {
                    $prompt = $aipstx_style_text . ' ' . $prompt;
                }
            }

            $image_count = isset($_POST['image_count']) ? intval($_POST['image_count']) : 1;
            $engine = isset($_POST['engine']) ? sanitize_text_field($_POST['engine']) : 'openai';
            $resolution = isset($_POST['resolution']) ? sanitize_text_field($_POST['resolution']) : '1024x1024';

            if ($engine == 'dall-e3') {
                $aipstx_openai_key = get_option('aipstx_openai_key');
                if (empty($aipstx_openai_key)) {
                    wp_send_json_error('Missing OpenAI API key for DALL-E 3');
                    wp_die();
                }

                $api_url = 'https://api.openai.com/v1/images/generations';
                $args = array(
                    'method' => 'POST',
                    'timeout' => 90,
                    'headers' => array(
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $aipstx_openai_key
                    ),
                    'body' => wp_json_encode(['prompt' => $prompt, 'n' => $image_count, 'size' => $resolution])
                );

                $response = wp_remote_post($api_url, $args);

                if (is_wp_error($response)) {
                    wp_send_json_error('Error creating images: ' . $response->get_error_message());
                    wp_die();
                }

                $response_data = json_decode(wp_remote_retrieve_body($response), true);

                if (isset($response_data['data'])) {
                    $images = array_map(function ($item) {
                        return $item['url'];
                    }, $response_data['data']);

                    wp_send_json_success($images);
                } else {
                    wp_send_json_error('Error retrieving images from OpenAI DALL-E 3.');
                }
            } else {
                $aipstx_edenai_key = get_option('aipstx_edenai_key');
                if (empty($aipstx_edenai_key)) {
                    wp_send_json_error('Missing EdenAI API key for the selected engine');
                    wp_die();
                }

                $api_url = 'https://api.edenai.run/v2/image/generation';
                $args = array(
                    'method' => 'POST',
                    'timeout' => 90,
                    'headers' => array(
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $aipstx_edenai_key
                    ),
                    'body' => wp_json_encode([
                        "response_as_dict" => true,
                        "attributes_as_list" => false,
                        "show_original_response" => false,
                        "resolution" => $resolution,
                        "num_images" => $image_count,
                        "providers" => $engine,
                        "text" => $prompt
                    ])
                );

                $response = wp_remote_post($api_url, $args);

                $response_body = wp_remote_retrieve_body($response);

                if (is_wp_error($response)) {
                    wp_send_json_error('Error creating images: ' . $response->get_error_message());
                    wp_die();
                }

                $response_data = json_decode(wp_remote_retrieve_body($response), true);

                // Dinamik olarak sağlayıcı adı kullanarak yanıtı işleme
                if (!empty($response_data) && isset($response_data[$engine]) && isset($response_data[$engine]['status']) && $response_data[$engine]['status'] == 'success') {
                    $images = array_map(function ($item) {
                        return $item['image_resource_url'];
                    }, $response_data[$engine]['items']);

                    wp_send_json_success($images);
                } else {
                    wp_send_json_error('Error retrieving images from the selected engine.');
                }
            }

            wp_die();
        }

    }

    aipstx_genimg::get_instance();
}