<?php return array (
  'name' => '权限控制',
  'author' => '梦小记',
  'version' => '1.0.8',
  'description' => '后台权限与菜单授权模块，负责管理员、权限组、菜单节点和访问控制链路管理。',
  'identification' => 'Auth',
  'directory' => 'Auth',
  'type' => 'system',
  'domain' => 'n',
  'auth' => 'n',
  'addmodel' => 'n',
  'links' => array (
    public_path('views/modules/auth') => module_path('Auth', 'Resources/views'),
  ),
  'cmslimit' => '',
  'pluginlimit' => '',
  'package_type' => 'module',
  'level' => 'base',
  'installable' => true,
  'disableable' => false,
  'uninstallable' => false,
  'deletable' => false,
  'compatibility' => array (
    'cms' => '*',
    'php' => '*',
    'laravel' => '*',
  ),
  'dependencies' => array (
  ),
  'conflicts' => array (
  ),
  'data_policy' => array (
    'on_disable' => 'keep',
    'on_uninstall' => 'backup',
    'on_delete' => 'manual',
  ),
  'telemetry' => array (
    'install' => true,
    'uninstall' => true,
    'enable' => true,
    'disable' => true,
    'upgrade' => true,
    'usage' => true,
  ),
  'protection' => array (
    'mode' => 'none',
    'encrypted_allowed' => false,
    'protected_paths' => array (
    ),
  ),
  'ui' => array (
    'domain' => 'n',
    'auth' => 'n',
    'addmodel' => 'n',
    'links' => array (
      public_path('views/modules/auth') => module_path('Auth', 'Resources/views'),
    ),
  ),
  'legacy' => array (
    'cmslimit' => '',
    'pluginlimit' => '',
  ),
);
