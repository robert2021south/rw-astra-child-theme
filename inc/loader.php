<?php
/**
 * 模块加载器 - 按需加载所有功能模块
 */

// 加载模板管理器（不依赖任何插件）
require_once ASTRA_CHILD_PATH . '/inc/templates/class-template-manager.php';

// 加载资源管理器
require_once ASTRA_CHILD_PATH . '/inc/assets/class-assets-manager.php';

// 初始化管理器
new RW_Template_Manager();
new RW_Assets_Manager();

// 只在需要时加载 UsersWP 相关功能
if (defined('USERSWP_VERSION') || class_exists('UsersWP')) {
    require_once ASTRA_CHILD_PATH . '/inc/userswp/class-account-tabs.php';
    // 初始化
    new RW_Account_Tabs();
}