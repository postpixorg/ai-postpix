<?php

namespace AIPSTX;

if (!defined('ABSPATH'))
    exit;
if (!class_exists('\\AIPSTX\\AIPSTX_finder')) {
    class AIPSTX_finder {

        private static $instance = null;

        public static function get_instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }


        public function __construct() {


            add_action('wp_ajax_aipstx_find_prompt', array($this, 'aipstx_find_prompt'));
        }

        public function aipstx_find_prompt() {
            // Nonce kontrolü
            if (!check_ajax_referer('aipstx_nonce', 'nonce', false)) {
                wp_send_json_error(array('message' => 'Nonce verification failed.'));
                return;
            }
            // İstemci tarafından temizlenmiş post içeriği al
            $post_content = isset($_POST['postContent']) ? sanitize_textarea_field($_POST['postContent']) : '';

            // OpenAI ve Eden AI API anahtarlarını al
            $aipstx_openai_key = get_option('aipstx_openai_key');
            $aipstx_edenai_key = get_option('aipstx_edenai_key');
            $prompt_engine = get_option('aipstx_prompt_engine', 'gpt-3.5-turbo');
            $engine = isset($_POST['engine']) ? sanitize_text_field($_POST['engine']) : '';

            // Eğer ilgili API anahtarı yoksa hata ver ve işlemi durdur
            if ($prompt_engine === 'eden_ai' && empty($aipstx_edenai_key)) {
                wp_send_json_error('No Eden AI API key provided');
                wp_die();
            } elseif ($prompt_engine !== 'eden_ai' && empty($aipstx_openai_key)) {
                wp_send_json_error('No OpenAI API key provided');
                wp_die();
            }

            $api_url = '';
            $args = [];

            // Prompt engine değerine göre API isteğini yap
            if ($prompt_engine === 'eden_ai') {
                // Eden AI API isteği yap
                $api_url = 'https://api.edenai.run/v2/text/chat';
                $args = array(
                    'method' => 'POST',
                    'timeout' => '60',
                    'headers' => array(
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $aipstx_edenai_key
                    ),
                    'body' => wp_json_encode([
                        "providers" => "google",
                        "text" => "Please analyze the following text and create a " . $engine . " prompt in English that will generate the most relevant and descriptive image that best summarizes the main topic and themes of the content. Text: " . $post_content
                    ])
                );
            } else {
                // OpenAI API isteği yap
                $api_url = 'https://api.openai.com/v1/chat/completions';
                $args = array(
                    'method' => 'POST',
                    'timeout' => '60',
                    'headers' => array(
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $aipstx_openai_key
                    ),
                    'body' => wp_json_encode([
                        "model" => $prompt_engine,
                        "messages" => [
                            [
                                "role" => "system",
                                "content" => "You are a prompt engineer. You will be given a block of text and your task will be to create a " . $engine . " prompt to create a featured image that best fits this text."
                            ],
                            [
                                "role" => "user",
                                "content" => $post_content
                            ]
                        ]
                    ])
                );
            }

            // API isteğini yap
            if (!empty($api_url)) {
                $response = wp_remote_request($api_url, $args);

                // API yanıtını kontrol et ve ekrana yazdır
                if (is_wp_error($response)) {
                    wp_send_json_error('No response from API');
                } else {
                    // API'den alınan yanıtı işle
                    $decoded_response = json_decode(wp_remote_retrieve_body($response), true);
                    $prompt = '';
                    if ($prompt_engine === 'eden_ai' && isset($decoded_response['google']['generated_text'])) {
                        $prompt = $decoded_response['google']['generated_text'];
                    } elseif (isset($decoded_response['choices'][0]['message']['content'])) {
                        $prompt = $decoded_response['choices'][0]['message']['content'];
                    }

                    // ":" işaretinden sonraki metni al
                    $colonPos = strpos($prompt, ':');
                    if ($colonPos !== false) {
                        $prompt = trim(substr($prompt, $colonPos + 1));
                    }

                    // Çift ve tek tırnak işaretlerini sil
                    $prompt = str_replace(['"', "'", "*", ":"], '', $prompt);

                    if (!empty($prompt)) {
                        wp_send_json_success($prompt);
                    } else {
                        wp_send_json_error('Invalid response from API');
                    }
                }
            } else {
                wp_send_json_error('No valid API key provided');
            }
        }

    }

    aipstx_finder::get_instance();
}
