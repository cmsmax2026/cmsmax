<?php return array (
  'name' => '通用内容管理',
  'author' => '梦小记',
  'version' => '2.1.4',
  'description' => '动态模型与内容表单引擎，负责字段结构、模型绑定、内容录入与前后台表单渲染。',
  'identification' => 'Formtools',
  'directory' => 'Formtools',
  'type' => 'function',
  'domain' => 'n',
  'auth' => 'y',
  'addmodel' => 'n',
  'links' => array (
    public_path('views/modules/formtools') => module_path('Formtools', 'Resources/views'),
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
    'addmodel' => 'n',
    'links' => array (
      public_path('views/modules/formtools') => module_path('Formtools', 'Resources/views'),
    ),
  ),
  'legacy' => array (
    'cmslimit' => '',
    'pluginlimit' => '',
  ),
);
