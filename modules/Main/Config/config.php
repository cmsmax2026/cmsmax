<?php return array (
  'name' => '主程序',
  'author' => '梦小记',
  'version' => '1.0.3',
  'description' => 'CMS 核心运行时与后台入口模块，负责基础路由、后台工作台、模块装载与系统主流程调度。',
  'identification' => 'Main',
  'directory' => 'Main',
  'type' => 'system',
  'domain' => 'n',
  'auth' => 'n',
  'addmodel' => 'n',
  'links' => array (
    public_path('views/modules/main') => module_path('Main', 'Resources/views'),
  ),
  'cmslimit' => '',
  'pluginlimit' => '',
  'package_type' => 'module',
  'level' => 'core',
  'installable' => true,
  'disableable' => false,
  'uninstallable' => false,
  'deletable' => false,
  'compatibility' =>
  array (
    'cms' => '*',
    'php' => '*',
    'laravel' => '*',
  ),
  'dependencies' =>
  array (
  ),
  'conflicts' =>
  array (
  ),
  'data_policy' =>
  array (
    'on_disable' => 'keep',
    'on_uninstall' => 'backup',
    'on_delete' => 'manual',
  ),
  'telemetry' =>
  array (
    'install' => true,
    'uninstall' => true,
    'enable' => true,
    'disable' => true,
    'upgrade' => true,
    'usage' => true,
  ),
  'protection' =>
  array (
    'mode' => 'none',
    'encrypted_allowed' => false,
    'protected_paths' =>
    array (
    ),
  ),
  'ui' =>
  array (
    'domain' => 'n',
    'auth' => 'n',
    'addmodel' => 'n',
    'links' => array (
    public_path('views/modules/main') => module_path('Main', 'Resources/views'),
  ),
  ),
  'legacy' =>
  array (
    'cmslimit' => '',
    'pluginlimit' => '',
  ),
);
