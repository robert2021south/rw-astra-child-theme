<?php

/**
 * 自定义页面模板配置
 *
 * 格式：
 * '模板文件路径' => [
 *     'name'        => '模板显示名称',
 *     'path'        => '模板文件在主题中的相对路径',
 *     'description' => '模板描述（可选）',
 *     'category'    => '模板分类（可选）',
 * ]
 *
 * @package Astra-Child
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

return [
    // 订单完成页面模板
    'page-templates/order-complete.php' => [
        'name' => __('Order Complete', 'astra-child'),
        'path' => 'page-templates/order-complete.php',
        'description' => __('thanks and delivery page for checkout', 'astra-child'),
        'category' => 'orders',
    ],

    // 订单详情页面模板
    'inc/userswp/templates/order-detail.php' => [
        'name' => __('Order Detail', 'astra-child'),
        'path' => 'inc/userswp/templates/order-detail.php',
        'description' => __('display the detail for a single order ', 'astra-child'),
        'category' => 'orders',
    ],

    // 如果你还有 UsersWP 相关的模板，但不想混在一起，可以这样：
//    'userswp/templates/licenses-tab.php' => [
//        'name' => __('UserWP Licenses', 'astra-child'),
//        'path' => 'inc/userswp/templates/licenses-tab.php',
//        'description' => __('用户许可证管理页面', 'astra-child'),
//        'category' => 'userswp',
//    ],
//
//    'userswp/templates/orders-tab.php' => [
//        'name' => __('UserWP Orders', 'astra-child'),
//        'path' => 'inc/userswp/templates/orders-tab.php',
//        'description' => __('用户订单列表页面', 'astra-child'),
//        'category' => 'userswp',
//    ],
//
//    // 未来可以继续添加其他模板
//    'templates/contact.php' => [
//        'name' => __('Custom Contact Page', 'astra-child'),
//        'path' => 'inc/templates/contact.php',
//        'description' => __('自定义联系页面模板', 'astra-child'),
//        'category' => 'general',
//    ],
];