<?php

/**
 * UsersWP 账户菜单项管理
 */

class RW_Account_Tabs
{

    public function __construct()
    {
        add_filter('uwp_account_available_tabs', [$this, 'add_custom_tabs']);
        add_filter('uwp_account_available_tabs', [$this, 'reorder_tabs'], 20);
        add_filter('uwp_account_navigation_tabs', [$this, 'hide_navigation_tabs']);
        add_action('uwp_account_form_display', [$this, 'render_tab_content']);
        //add_filter('uwp_account_hooks', [$this, 'add_my_licenses_tab']);
        add_filter('uwp_account_page_title', [$this, 'override_account_title'], 10, 2);
    }

    function override_account_title($title, $type) {

        if ($type == 'my_licenses') {
            return __('My Licenses', 'astra-child');
        }

        if ($type == 'my_orders') {
            return __('My Orders', 'astra-child');
        }

        if ($type == 'order_detail') {
            return __('Order Detail', 'astra-child');
        }

        return $title;
    }

    public function add_custom_tabs($tabs)
    {
        $tabs['my_licenses'] = [
            'title' => __('My Licenses', 'astra-child'),
            'icon' => 'fas fa-key',
        ];
        $tabs['my_orders'] = [
            'title' => __('My Orders', 'astra-child'),
            'icon' => 'fas fa-shopping-cart',
        ];
        $tabs['order_detail'] = [
            'title' => __('Order Detail', 'astra-child'),
            'icon' => 'fas fa-receipt',
        ];
        return $tabs;
    }

    /**
     * 隐藏导航中的 tab
     */
    public function hide_navigation_tabs($tabs)
    {
        unset($tabs['order_detail']);
        return $tabs;
    }


    public function reorder_tabs($tabs): array
    {
        $ordered = [];
        $priority = ['my_licenses', 'my_orders', 'account', 'notifications', 'privacy', 'change-password'];

        foreach ($priority as $key) {
            if (isset($tabs[$key])) {
                $ordered[$key] = $tabs[$key];
            }
        }
        return $ordered;
    }

    public function render_tab_content($active_tab): void
    {

        // 只处理自定义标签页
        if ($active_tab === 'my_licenses') {
            include ASTRA_CHILD_PATH . '/inc/userswp/templates/licenses.php';
            return;
        }

        if ($active_tab === 'my_orders') {
            include ASTRA_CHILD_PATH . '/inc/userswp/templates/orders.php';
        }

        if ($active_tab === 'order_detail') {
            include ASTRA_CHILD_PATH . '/inc/userswp/templates/order-detail.php';
            return;
        }

    }

}