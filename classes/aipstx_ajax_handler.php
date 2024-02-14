<?php

namespace AIPSTX;

if (!defined('ABSPATH'))
    exit;
if (!class_exists('\\AIPSTX\\AIPSTX_AjaxHandler')) {
    class AIPSTX_AjaxHandler {

        public static function localizeScripts($handle) {
            wp_localize_script($handle, 'aipstxAjax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('aipstx_nonce')
            ));
        }
    }
}
