<?php
/**
 * Astra Child Theme functions and definitions
 */

/**
 * Astra Child Theme Functions
 */

// 定义主题常量
const ASTRA_CHILD_VERSION = '1.0.0';
define('ASTRA_CHILD_PATH', get_stylesheet_directory());
define('ASTRA_CHILD_URL', get_stylesheet_directory_uri());

// 加载模块加载器
require_once ASTRA_CHILD_PATH . '/inc/loader.php';