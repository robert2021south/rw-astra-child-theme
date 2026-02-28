<?php

/**
 * API 请求处理
 */

class RW_API_Client
{
    private $api_url = 'https://api.robertwp.com/api';
    private $token;

    public function __construct()
    {
        $this->token = $this->get_token();
    }

    public function get_user_licenses($user_id, $email)
    {
        return $this->request('/user/licenses', [
            'user_id' => $user_id,
            'email' => $email
        ]);
    }

    public function get_user_orders($user_id, $email)
    {
        return $this->request('/user/orders', [
            'user_id' => $user_id,
            'email' => $email
        ]);
    }

    private function request($endpoint, $data)
    {
        if (!$this->token) {
            return ['error' => 'Unable to authenticate'];
        }

        $response = wp_remote_post($this->api_url . $endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data),
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    private function get_token()
    {
        $cached = get_transient('rw_api_token');
        if ($cached) {
            return $cached;
        }

        $response = wp_remote_post($this->api_url . '/auth/issue-delivery-token', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode(['transaction_id' => 'wp_' . uniqid()]),
            'timeout' => 5,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        $token = $data['data']['token'] ?? false;

        if ($token) {
            set_transient('rw_api_token', $token, 300);
        }

        return $token;
    }
}