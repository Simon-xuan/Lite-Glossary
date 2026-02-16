<?php
/**
 * Plugin Name: 轻量级词汇表
 * Plugin URI: https://example.com/lite-glossary
 * Description: 一个轻量级的 WordPress 词汇表插件，为内容中的术语添加工具提示。
 * Version: 1.0.0
 * Author: 您的名称
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: lite-glossary
 * Domain Path: /languages
 */

// 启用输出缓冲，解决 Header 报错问题
ob_start();

// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 定义插件常量
define( 'LITE_GLOSSARY_VERSION', '1.01' );
define( 'LITE_GLOSSARY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LITE_GLOSSARY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// 加载必要的文件
require_once LITE_GLOSSARY_PLUGIN_DIR . 'includes/post-type.php';
require_once LITE_GLOSSARY_PLUGIN_DIR . 'includes/content-filter.php';
require_once LITE_GLOSSARY_PLUGIN_DIR . 'includes/admin-page.php';

// 加载资源
function lite_glossary_enqueue_assets() {
    // 仅在单个文章/页面加载
    if ( is_singular() ) {
        // 加载 CSS
        wp_enqueue_style(
            'lite-glossary-tooltip',
            LITE_GLOSSARY_PLUGIN_URL . 'assets/css/tooltip.css',
            array(),
            LITE_GLOSSARY_VERSION
        );
        
        // 加载 JS
        wp_enqueue_script(
            'lite-glossary-tooltip',
            LITE_GLOSSARY_PLUGIN_URL . 'assets/js/tooltip.js',
            array(),
            LITE_GLOSSARY_VERSION,
            true // 在页脚加载
        );
    }
}
add_action( 'wp_enqueue_scripts', 'lite_glossary_enqueue_assets' );

// 注册激活钩子
function lite_glossary_activation() {
    // 注册文章类型
    lite_glossary_register_post_type();
    
    // 刷新重写规则
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'lite_glossary_activation' );

// 注册停用钩子
function lite_glossary_deactivation() {
    // 刷新重写规则
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'lite_glossary_deactivation' );