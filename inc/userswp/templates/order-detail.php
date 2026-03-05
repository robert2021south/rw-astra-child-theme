<?php
/**
 * Template Name: Order Detail
 * Description: 用于展示单个订单的详细信息，集成 UsersWP 用户中心布局
 *
 * @package Astra-Child
 * @subpackage UsersWP
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="uwp-profile-tab-content rw-order-detail-tab">
    <div class="rw-loading-container" style="text-align: center; padding: 40px;">
        <i class="fa fa-spinner fa-spin fa-3x" style="color: #1c3faa;"></i>
        <p style="margin-top: 15px; color: #666;"><?php _e('Loading order detail...', 'astra-child'); ?></p>
    </div>
</div>
