<?php
/**
 * 授权处理器 - 管理用户授权展示和操作
 * 主要职责： 获取授权列表、格式化显示、处理授权码掩码、激活站点管理
 *
 * @package Astra-Child
 * @subpackage UsersWP
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

class RW_License_Handler
{

    /**
     * API客户端实例
     * @var RW_API_Client
     */
    private $api;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->api = new RW_API_Client();

        // 注册AJAX处理
        add_action('wp_ajax_rw_get_license_details', [$this, 'ajax_get_license_details']);
        add_action('wp_ajax_rw_deactivate_site', [$this, 'ajax_deactivate_site']);
    }

    /**
     * 获取用户授权列表
     *
     * @param int $user_id WordPress用户ID
     * @param string $email 用户邮箱
     * @return array 授权列表或错误信息
     */
    public function get_user_licenses($user_id, $email)
    {
        $data = $this->api->get_user_licenses($user_id, $email);

        if (isset($data['error'])) {
            return [
                'error' => $data['error'],
                'licenses' => []
            ];
        }

        return [
            'licenses' => $data['licenses'] ?? [],
            'total' => $data['total'] ?? 0
        ];
    }

    /**
     * 格式化授权数据用于前端展示
     *
     * @param array $license 原始授权数据
     * @return array 格式化后的数据
     */
    public function format_license_for_display($license)
    {
        return [
            'id' => $license['id'] ?? 0,
            'plugin_name' => $license['plugin_name'] ?? 'Unknown Plugin',
            'plugin_slug' => $license['plugin_slug'] ?? '',
            'license_key' => $license['license_key'] ?? '',
            'license_key_masked' => $this->mask_license_key($license['license_key'] ?? ''),
            'status' => $license['status'] ?? 'inactive',
            'purchase_date' => isset($license['created_at']) ? date_i18n(get_option('date_format'), strtotime($license['created_at'])) : '',
            'expiry_date' => isset($license['expires_at']) ? date_i18n(get_option('date_format'), strtotime($license['expires_at'])) : null,
            'activations' => $this->format_activations($license['activations'] ?? []),
            'max_activations' => $license['max_activations'] ?? 3,
            'remaining_activations' => $this->calculate_remaining_activations($license)
        ];
    }

    /**
     * 格式化激活站点列表
     *
     * @param array $activations 原始激活数据
     * @return array 格式化后的激活列表
     */
    private function format_activations($activations)
    {
        $formatted = [];

        foreach ($activations as $activation) {
            $formatted[] = [
                'id' => $activation['id'] ?? 0,
                'site_url' => $activation['site_url'] ?? '',
                'site_domain' => parse_url($activation['site_url'] ?? '', PHP_URL_HOST) ?: $activation['site_url'],
                'activated_at' => isset($activation['created_at']) ? date_i18n(get_option('date_format'), strtotime($activation['created_at'])) : '',
                'last_verified' => isset($activation['last_verified_at']) ?
                    human_time_diff(strtotime($activation['last_verified_at']), current_time('timestamp')) . ' ago' : 'Never',
                'is_active' => $activation['is_active'] ?? true
            ];
        }

        return $formatted;
    }

    /**
     * 计算剩余激活次数
     *
     * @param array $license 授权数据
     * @return int
     */
    private function calculate_remaining_activations($license)
    {
        $max = $license['max_activations'] ?? 3;
        $used = count($license['activations'] ?? []);
        return max(0, $max - $used);
    }

    /**
     * 掩码处理授权码（中间部分用星号代替）
     *
     * @param string $key 原始授权码
     * @return string
     */
    private function mask_license_key($key)
    {
        if (strlen($key) < 12) {
            return $key;
        }

        $prefix = substr($key, 0, 8);
        $suffix = substr($key, -4);
        $masked = $prefix . str_repeat('*', strlen($key) - 12) . $suffix;

        return $masked;
    }

    /**
     * AJAX处理：获取授权详情
     */
    public function ajax_get_license_details()
    {
        // 验证nonce
        if (!check_ajax_referer('rw_license_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        $license_id = intval($_POST['license_id'] ?? 0);
        if (!$license_id) {
            wp_send_json_error(['message' => 'Invalid license ID']);
        }

        // 这里可以添加从API获取单个授权详情的逻辑
        // 目前简单返回授权ID，实际应用中需要调用API

        wp_send_json_success([
            'license_id' => $license_id,
            'details' => 'License details would be fetched here'
        ]);
    }

    /**
     * AJAX处理：停用站点
     */
    public function ajax_deactivate_site()
    {
        // 验证nonce
        if (!check_ajax_referer('rw_license_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        $activation_id = intval($_POST['activation_id'] ?? 0);
        if (!$activation_id) {
            wp_send_json_error(['message' => 'Invalid activation ID']);
        }

        // 这里可以添加调用API停用站点的逻辑

        wp_send_json_success([
            'message' => 'Site deactivated successfully',
            'activation_id' => $activation_id
        ]);
    }

    /**
     * 渲染授权列表HTML
     *
     * @param array $licenses 授权数据
     * @return string
     */
    public function render_licenses_html($licenses)
    {
        ob_start();
        include ASTRA_CHILD_PATH . '/inc/userswp/templates/licenses-tab.php';
        return ob_get_clean();
    }
}