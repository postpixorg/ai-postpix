<?php
if (!defined('ABSPATH')) exit;
require_once __DIR__ . '/classes/aipstx_util.php';
require_once __DIR__ . '/classes/aipstx_ajax_handler.php';
require_once __DIR__ . '/classes/aipstx_fprompt.php';
require_once __DIR__ . '/classes/aipstx_genimg.php';
require_once __DIR__ . '/classes/aipstx_btn.php';
require_once __DIR__ . '/classes/aipstx_metabox.php';
require_once __DIR__ . '/classes/aipstx_hook.php';
if (\AIPSTX\aipstx_util_core()->aipstx_is_pro()) {
    if (file_exists(__DIR__ . '/lib/aipstx__premium_only.php')) {
        require_once __DIR__ . '/lib/aipstx__premium_only.php';
    }
}
