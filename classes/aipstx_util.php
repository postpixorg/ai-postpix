<?php

namespace AIPSTX;

if (!defined('ABSPATH'))
    exit;
if (!class_exists('\\AIPSTX\\AIPSTX_util')) {
    class AIPSTX_util {
        private static $instance = null;

        public static function get_instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }


        public function aipstx_is_pro() {
            return aipstx_fs()->is_plan__premium_only('pro');
        }
    }
}
if (!function_exists(__NAMESPACE__ . '\aipstx_util_core')) {
    function aipstx_util_core() {
        return aipstx_util::get_instance();
    }
}
