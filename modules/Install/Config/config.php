<?php return array (
  'name' => '安装助手',
  'author' => '梦小记',
  'version' => '1.0.3',
  'description' => '安装与初始化模块，负责环境检测、数据库初始化、站点安装向导与首次部署引导。',
  'identification' => 'Install',
  'directory' => 'Install',
  'type' => 'function',
  'domain' => 'n',
  'auth' => 'n',
  'addmodel' => 'n',
  'links' => array (
    public_path('views/modules/install') => module_path('Install', 'Resources/views'),
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
    public_path('views/modules/install') => module_path('Install', 'Resources/views'),
  ),
  ),
  'legacy' =>
  array (
    'cmslimit' => '',
    'pluginlimit' => '',
  ),
);
