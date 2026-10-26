<?php return array (
  'name' => '统一账号管理',
  'author' => '梦小记',
  'version' => '1.0.4',
  'description' => '统一会员与账号资产模块，提供会员资料、等级、钱包积分、实名与提现等能力。',
  'identification' => 'Member',
  'directory' => 'Member',
  'type' => 'function',
  'domain' => 'n',
  'auth' => 'y',
  'addmodel' => 'y',
  'links' => array (
    public_path('views/modules/member') => module_path('Member', 'Resources/views'),
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
    'auth' => 'y',
    'addmodel' => 'y',
    'links' => array (
      public_path('views/modules/member') => module_path('Member', 'Resources/views'),
    ),
  ),
  'legacy' => array (
    'cmslimit' => '',
    'pluginlimit' => '',
  ),
);
