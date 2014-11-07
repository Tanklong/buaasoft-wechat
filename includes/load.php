<?php
/**
 * Used to set up common variables and include the procedural and class library.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

require_once ABSPATH . 'includes/InputType.php';
require_once ABSPATH . 'includes/UserInput.php';
require_once ABSPATH . 'includes/BaseModule.php';
require_once ABSPATH . 'includes/OutputFormatter.php';
require_once ABSPATH . 'includes/MessageReceiver.php';
require_once ABSPATH . 'includes/module.php';
require_once ABSPATH . 'includes/functions.php';
require_once ABSPATH . 'includes/wxdb.php';

// Constants
define('OBJECT', 'OBJECT');
define('OBJECT_K', 'OBJECT_K');
define('ARRAY_A', 'ARRAY_A');
define('ARRAY_N', 'ARRAY_N');

// Globals
$modules = array();
$actions = array();
$global_options = array(
    'general' => '通用设置',
    'users' => '用户管理',
    'modules' => '模块管理',
    'install_module' => '安装模块',
    'debug' => '系统调试'
);
$global_option_icons = array(
    'general' => 'dashboard',
    'users' => 'user',
    'modules' => 'plug',
    'install_module' => 'plus',
    'debug' => 'wrench'
);
$wxdb = null;
$time_start = 0.0;
$time_end = 0.0;

date_default_timezone_set('Asia/Shanghai');
require_db();
load_modules(get_modules());
timer_start();