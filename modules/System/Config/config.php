<?php return array (
  'name' => '系统设置',
  'author' => '梦小记',
  'version' => '1.1.1',
  'description' => '系统设置与运维控制模块，提供安全、上传、缓存、计划任务、SEO 与全局参数管理能力。',
  'identification' => 'System',
  'directory' => 'System',
  'type' => 'function',
  'domain' => 'n',
  'auth' => 'y',
  'addmodel' => 'n',
  'links' => array (
    public_path('views/modules/system') => module_path('System', 'Resources/views'),
  ),
  'cmslimit' => '',
  'pluginlimit' => '',
  'package_type' => 'module',
  'level' => 'core',
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
    'auth' => 'y',
    'addmodel' => 'n',
    'links' => array (
      public_path('views/modules/system') => module_path('System', 'Resources/views'),
    ),
  ),
  'legacy' => array (
    'cmslimit' => '',
    'pluginlimit' => '',
  ),
);
