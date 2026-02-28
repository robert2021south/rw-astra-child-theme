<?php
/**
 * 订单处理器 - 管理用户订单展示
 * 主要职责： 获取订单列表、格式化金额、状态徽章、分页加载
 *
 * @package Astra-Child
 * @subpackage UsersWP
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

class RW_Order_Handler {

    /**
     * API客户端实例
     * @var RW_API_Client
     */
    private $api;

    /**
     * 构造函数
     */
    public function __construct() {
        $this->api = new RW_API_Client();

        // 注册AJAX处理
        add_action('wp_ajax_rw_load_more_orders', [$this, 'ajax_load_more_orders']);
    }

    /**
     * 获取用户订单列表
     *
     * @param int $user_id WordPress用户ID
     * @param string $email 用户邮箱
     * @param int $page 页码
     * @param int $per_page 每页数量
     * @return array 订单列表或错误信息
     */
    public function get_user_orders($user_id, $email, $page = 1, $per_page = 10) {
        $data = $this->api->get_user_orders($user_id, $email, $page, $per_page);

        if (isset($data['error'])) {
            return [
                'error' => $data['error'],
                'orders' => [],
                'total' => 0,
                'current_page' => 1,
                'total_pages' => 1
            ];
        }

        return [
            'orders' => $this->format_orders($data['orders'] ?? []),
            'total' => $data['total'] ?? 0,
            'current_page' => $data['current_page'] ?? $page,
            'total_pages' => $data['total_pages'] ?? 1
        ];
    }

    /**
     * 格式化订单数据用于前端展示
     *
     * @param array $orders 原始订单数据
     * @return array
     */
    private function format_orders($orders) {
        $formatted = [];

        foreach ($orders as $order) {
            $formatted[] = [
                'id' => $order['id'] ?? 0,
                'transaction_id' => $order['transaction_id'] ?? '',
                'plugin_name' => $order['plugin_name'] ?? 'Unknown Product',
                'amount' => $order['amount'] ?? 0,
                'refunded_amount' => $order['refunded_amount'] ?? 0,
                'currency' => $order['currency'] ?? 'USD',
                'status' => $order['status'] ?? 'pending',
                'created_at' => $order['created_at'] ?? '',
                'items' => $this->format_order_items($order['items'] ?? []),
                'payment_method' => $order['payment_method'] ?? 'Paddle',
                'invoice_url' => $order['invoice_url'] ?? null
            ];
        }

        return $formatted;
    }

    /**
     * 格式化订单商品
     *
     * @param array $items 原始商品数据
     * @return array
     */
    private function format_order_items($items) {
        $formatted = [];

        foreach ($items as $item) {
            $formatted[] = [
                'name' => $item['name'] ?? 'Product',
                'quantity' => $item['quantity'] ?? 1,
                'price' => $item['price'] ?? 0,
                'total' => $item['total'] ?? 0
            ];
        }

        return $formatted;
    }

    /**
     * 获取订单状态徽章的CSS类
     *
     * @param string $status 订单状态
     * @return string
     */
    public function get_status_badge_class($status) {
        $classes = [
            'paid' => 'rw-status-success',
            'completed' => 'rw-status-success',
            'pending' => 'rw-status-warning',
            'refunded' => 'rw-status-info',
            'cancelled' => 'rw-status-error',
            'failed' => 'rw-status-error'
        ];

        return $classes[$status] ?? 'rw-status-default';
    }

    /**
     * 获取状态显示文本
     *
     * @param string $status 订单状态
     * @return string
     */
    public function get_status_display_text($status) {
        $texts = [
            'paid' => __('Paid', 'astra-child'),
            'completed' => __('Completed', 'astra-child'),
            'pending' => __('Pending', 'astra-child'),
            'refunded' => __('Refunded', 'astra-child'),
            'cancelled' => __('Cancelled', 'astra-child'),
            'failed' => __('Failed', 'astra-child')
        ];

        return $texts[$status] ?? ucfirst($status);
    }

    /**
     * 格式化金额
     *
     * @param int $amount 金额（分）
     * @param string $currency 货币代码
     * @return string
     */
    public function format_amount($amount, $currency = 'USD') {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CNY' => '¥'
        ];

        $symbol = $symbols[$currency] ?? '$';
        $formatted = number_format($amount / 100, 2);

        return $symbol . $formatted;
    }

    /**
     * AJAX处理：加载更多订单
     */
    public function ajax_load_more_orders() {
        // 验证nonce
        if (!check_ajax_referer('rw_orders_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        $page = intval($_POST['page'] ?? 1);
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        if (!$user_id) {
            wp_send_json_error(['message' => 'User not logged in']);
        }

        $orders_data = $this->get_user_orders($user_id, $user->user_email, $page);

        if (isset($orders_data['error'])) {
            wp_send_json_error(['message' => $orders_data['error']]);
        }

        ob_start();
        foreach ($orders_data['orders'] as $order) {
            $this->render_order_card($order);
        }
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html,
            'current_page' => $orders_data['current_page'],
            'total_pages' => $orders_data['total_pages'],
            'has_more' => $orders_data['current_page'] < $orders_data['total_pages']
        ]);
    }

    /**
     * 渲染单个订单卡片
     *
     * @param array $order 订单数据
     */
    private function render_order_card($order) {
        ?>
        <div class="rw-order-card">
            <!-- 订单卡片HTML，与模板文件中的一致 -->
            <!-- 这里可以放你的订单卡片HTML结构 -->
        </div>
        <?php
    }

    /**
     * 渲染订单列表HTML
     *
     * @param array $orders 订单数据
     * @return string
     */
    public function render_orders_html($orders) {
        ob_start();
        include ASTRA_CHILD_PATH . '/inc/userswp/templates/orders-tab.php';
        return ob_get_clean();
    }
}