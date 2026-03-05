<?php

/**
 * 自定义页面模板管理器
 *
 * @package Astra-Child
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

class RW_Template_Manager
{

    /**
     * 模板配置数组
     *
     * @var array
     */
    private array $templates = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->load_template_config();
        $this->register_hooks();
    }

    /**
     * 加载模板配置
     */
    private function load_template_config(): void
    {
        // 从配置文件加载模板列表
        $config_file = ASTRA_CHILD_PATH . '/inc/templates/config/templates.php';
        if (file_exists($config_file)) {
            $this->templates = include $config_file;
        }

        /**
         * 允许其他插件/主题通过过滤器添加模板
         */
        $this->templates = apply_filters('rw_custom_templates', $this->templates);
    }

    /**
     * 注册 WordPress 钩子
     */
    private function register_hooks(): void
    {
        // 添加模板到页面选择列表
        add_filter('theme_page_templates', [$this, 'add_page_templates']);

        // 加载自定义模板
        add_filter('template_include', [$this, 'load_custom_template']);

        // 添加模板说明（可选）
        add_action('admin_head', [$this, 'add_template_help_text']);
    }

    /**
     * 添加自定义模板到页面编辑器的下拉列表
     *
     * @param array $templates 现有模板列表
     * @return array
     */
    public function add_page_templates(array $templates): array
    {
        foreach ($this->templates as $template_key => $template_config) {
            $templates[$template_key] = $template_config['name'];
        }

        return $templates;
    }

    /**
     * 加载自定义模板
     *
     * @param string $template 当前模板路径
     * @return string
     */
    public function load_custom_template(string $template): string
    {
        // 如果不是页面，直接返回
        if (!is_page()) {
            return $template;
        }

        // 获取当前页面选择的模板
        $template_slug = get_page_template_slug();

        // 检查这个模板是否在我们的配置中
        if (isset($this->templates[$template_slug])) {
            $template_config = $this->templates[$template_slug];

            // 构建模板文件路径
            $new_template = ASTRA_CHILD_PATH . '/' . $template_config['path'];

            // 检查文件是否存在
            if (file_exists($new_template)) {
                return $new_template;
            }

            // 如果文件不存在，记录错误
            $this->log_template_error($template_slug, $new_template);
        }

        return $template;
    }

    /**
     * 获取模板配置
     *
     * @param string $template_slug 模板标识
     * @return array|null
     */
    public function get_template_config(string $template_slug): ?array
    {
        return $this->templates[$template_slug] ?? null;
    }

    /**
     * 获取所有模板配置
     *
     * @return array
     */
    public function get_all_templates(): array
    {
        return $this->templates;
    }

    /**
     * 添加模板说明文本到后台
     */
    public function add_template_help_text(): void
    {
        global $post_type;

        if ($post_type !== 'page') {
            return;
        }

        // 如果有需要显示的帮助文本
        $screen = get_current_screen();
        if ($screen && $screen->id === 'page') {
            foreach ($this->templates as $slug => $config) {
                if (!empty($config['description'])) {
                    // 这里可以添加帮助文本显示逻辑
                    // 例如：add_help_tab 或内联说明
                }
            }
        }
    }

    /**
     * 记录模板文件错误
     *
     * @param string $slug 模板标识
     * @param string $path 期望的文件路径
     */
    private function log_template_error(string $slug, string $path): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                'RW_Template_Manager: Template file not found. Slug: %s, Path: %s',
                $slug,
                $path
            ));
        }
    }
}
