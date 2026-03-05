<?php
/**
 * 前端资源管理器
 *
 * @package Astra-Child
 */

if (!defined('ABSPATH')) {
    exit;
}

class RW_Assets_Manager {

    public function __construct() {
        // 基础样式加载
        add_action('wp_enqueue_scripts', [$this, 'enqueue_parent_styles']);

        // 账户页面资源（从原 register-assets.php 迁移）
        add_action('wp_enqueue_scripts', [$this, 'rw_enqueue_account_tabs_assets']);

        // 订单完成页面资源（从 functions.php 迁移）
        add_action('wp_enqueue_scripts', [$this, 'enqueue_order_complete_assets']);
    }

    /**
     * 加载父主题样式
     */
    public function enqueue_parent_styles(): void
    {
        wp_enqueue_style(
            'astra-parent-style',
            get_template_directory_uri() . '/style.css'
        );

        wp_enqueue_style(
            'astra-child-style',
            get_stylesheet_uri(),
            ['astra-parent-style']
        );
    }

    /**
     * 账户页面资源加载（从原 register-assets.php 迁移）
     */
    public function rw_enqueue_account_tabs_assets(): void
    {
        // 只在账户页面加载
        if (!str_contains($_SERVER['REQUEST_URI'], '/account/')) {
            return;
        }

        // 获取并过滤 type 参数
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';

        // 只有 my_licenses 或 my_orders 页面才加载资源
        if (!in_array($type, ['my_licenses', 'my_orders', 'order_detail'], true)) {
            return;
        }

        // 准备本地化数据
        $localize_data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url(),
            'rest_nonce' => wp_create_nonce('wp_rest'),
            'user_id' => get_current_user_id(),
            'nonce' => wp_create_nonce('rw_account_nonce'),
            'is_logged_in' => is_user_logged_in(),
            'current_type' => $type
        );

        // 基础 CSS（共用样式）
        wp_enqueue_style(
            'rw-account-base-css',
            ASTRA_CHILD_URL . '/inc/assets/css/rw-account-base.css',
            array(),
            ASTRA_CHILD_VERSION
        );

        // 基础 JS（核心功能）
        wp_enqueue_script(
            'rw-account-base-js',
            ASTRA_CHILD_URL . '/inc/assets/js/rw-account-base.js',
            array('jquery'),
            ASTRA_CHILD_VERSION,
            true
        );

        // 为基础脚本添加本地化数据
        wp_localize_script('rw-account-base-js', 'rw_ajax', $localize_data);

        // 根据 type 参数加载特定的 CSS 和 JS
        if ($type === 'my_licenses') {
            // 加载 Licenses 页面特定 CSS
            wp_enqueue_style(
                'rw-account-licenses-css',
                ASTRA_CHILD_URL . '/inc/assets/css/rw-account-licenses.css',
                array('rw-account-base-css'),
                ASTRA_CHILD_VERSION
            );

            // 加载 Licenses 页面特定 JS
            wp_enqueue_script(
                'rw-account-licenses-js',
                ASTRA_CHILD_URL . '/inc/assets/js/rw-account-licenses.js',
                array('jquery', 'rw-account-base-js'),
                ASTRA_CHILD_VERSION,
                true
            );

        } elseif ($type === 'my_orders') {
            // 加载 Orders 页面特定 CSS
            wp_enqueue_style(
                'rw-account-orders-css',
                ASTRA_CHILD_URL . '/inc/assets/css/rw-account-orders.css',
                array('rw-account-base-css'),
                ASTRA_CHILD_VERSION
            );

            // 加载 Orders 页面特定 JS
            wp_enqueue_script(
                'rw-account-orders-js',
                ASTRA_CHILD_URL . '/inc/assets/js/rw-account-orders.js',
                array('jquery', 'rw-account-base-js'),
                ASTRA_CHILD_VERSION,
                true
            );
        }elseif ($type === 'order_detail') {
            wp_enqueue_style(
                'rw-order-detail-css',
                ASTRA_CHILD_URL . '/inc/assets/css/rw-account-order-detail.css',
                array('rw-account-base-css'),
                ASTRA_CHILD_VERSION
            );

            wp_enqueue_script(
                'rw-order-detail-js',
                ASTRA_CHILD_URL . '/inc/assets/js/rw-account-order-detail.js',
                array('jquery', 'rw-account-base-js'),
                ASTRA_CHILD_VERSION,
                true
            );

        }
    }


    /**
     * 订单完成页面资源加载（从 functions.php 迁移）
     */
    public function enqueue_order_complete_assets(): void
    {
        // 检查是否是订单完成页面
        if (!$this->is_order_complete_page()) {
            return;
        }

        $this->enqueue_order_complete_css();
        $this->enqueue_order_complete_js();
    }

    /**
     * 判断是否是订单完成页面
     */
    private function is_order_complete_page(): bool
    {
        // 通过模板检查
        if (is_page()) {
            $template_slug = get_page_template_slug();
            return $template_slug === 'page-templates/order-complete.php';
        }

        // 或者通过页面 slug 检查（二选一）
        // return is_page('order-complete');

        return false;
    }

    /**
     * 加载订单完成页面的 CSS
     */
    private function enqueue_order_complete_css(): void
    {
        $css_file = '/assets/css/order-complete.min.css';
        $css_path = ASTRA_CHILD_PATH . $css_file;

        if (file_exists($css_path)) {
            wp_enqueue_style(
                'order-complete-min-css',
                ASTRA_CHILD_URL . $css_file,
                [],
                filemtime($css_path)
            );
        }
    }

    /**
     * 加载订单完成页面的 JavaScript
     */
    private function enqueue_order_complete_js(): void
    {
        $js_file = '/assets/js/order-complete.min.js';
        $js_path = ASTRA_CHILD_PATH . $js_file;

        if (file_exists($js_path)) {
            wp_enqueue_script(
                'order-complete-min-js',
                ASTRA_CHILD_URL . $js_file,
                [], // 如果有依赖如 jQuery，可以添加 ['jquery']
                filemtime($js_path),
                true
            );

            // 传递数据到 JavaScript
            $this->localize_order_complete_script();
        }
    }

    /**
     * 本地化订单完成页面的脚本数据
     */
    private function localize_order_complete_script(): void
    {
        $transaction_id = isset($_GET['transaction_id']) ? sanitize_text_field($_GET['transaction_id']) : '';
        $plugin_slug = isset($_GET['slug']) ? sanitize_text_field($_GET['slug']) : '';
        $plugin_version = isset($_GET['version']) ? sanitize_text_field($_GET['version']) : '';

        wp_localize_script('order-complete-min-js', 'orderConfig', [
            'transactionId' => $transaction_id,
            'pluginSlug' => $plugin_slug,
            'pluginVersion' => $plugin_version,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('order_complete_nonce')
        ]);
    }
}