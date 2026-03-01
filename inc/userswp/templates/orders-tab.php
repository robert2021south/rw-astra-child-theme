<?php
/**
 * 订单列表模板
 * 实际内容由 rw-account-tabs.js 动态加载
 *
 * @package Astra-Child
 * @subpackage UsersWP
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="uwp-profile-tab-content rw-orders-tab">
    <!-- 初始加载状态，JS会替换这个内容 -->
    <div class="rw-loading-container" style="text-align: center; padding: 40px;">
        <i class="fa fa-spinner fa-spin fa-3x" style="color: #1c3faa;"></i>
        <p style="margin-top: 15px; color: #666;"><?php _e('Loading orders...', 'astra-child'); ?></p>
    </div>
</div>