<?php

namespace Modules\System\Database\Seeders;

use Illuminate\Database\Seeder;

class ModuleSystemSettingsSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        $rows = array (
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'limit_count',
     'value' => '5',
  ),
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'limit_time',
     'value' => '30',
  ),
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'blacklist_ip',
     'value' => '127.0.0.2,127.0.0.3',
  ),
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'whitelist_ip',
     'value' => '127.0.0.1',
  ),
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'ip_auto_ban_threshold',
     'value' => '10',
  ),
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'ip_auto_ban_minutes',
     'value' => '60',
  ),
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'admin_login_code',
     'value' => '0',
  ),
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'home_submit_code',
     'value' => '0',
  ),
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'admin_login_entrance',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'admin_ip_access_mode',
     'value' => 'allow_all',
  ),
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'admin_google_authenticator_mode',
     'value' => 'off',
  ),
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'admin_google_authenticator_issuer',
     'value' => 'MxzCMS Admin',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'upload_status',
     'value' => '1',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'upload_limit',
     'value' => '500000',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'upload_format',
     'value' => 'png,jpeg,jpg,gif,zip,rar,pdf,doc,docx,txt,xls,xlsx,avi,mp3,mp4',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'upload_driver',
     'value' => 'local',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'thumb_auto',
     'value' => '0',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'thumb_method',
     'value' => 'message',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'thumb_width',
     'value' => '400',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'thumb_height',
     'value' => '400',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'watermark_type',
     'value' => 'text',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'watermark_position',
     'value' => 'center',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'watermark_text',
     'value' => '梦小记CMS',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'watermark_text_size',
     'value' => '19',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'watermark_text_angle',
     'value' => '180',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'watermark_text_color',
     'value' => '#000000',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'watermark_upload_format',
     'value' => 'png,jpeg,jpg',
  ),
  array(
     'module' => 'Main',
     'type' => 'upload',
     'key' => 'watermark_img',
     'value' => '',
  ),
  array(
     'module' => 'Main',
     'type' => 'sms',
     'key' => 'sms_driver',
     'value' => '',
  ),
  array(
     'module' => 'Main',
     'type' => 'pay',
     'key' => 'pay_driver',
     'value' => '',
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'logo_animated',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'global_bgcolor',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'nav_bgcolor',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'nav_position',
     'value' => 'static',
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'page_width',
     'value' => 'container',
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'nav_color',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'preloader',
     'value' => 'off',
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'nav_hover_color',
     'value' => '#60a5fa',
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'nav_dropdown_bgcolor',
     'value' => '#0f172a',
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'nav_dropdown_color',
     'value' => '#e2e8f0',
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'logo_animation_speed',
     'value' => '6s',
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'logo_hover_pause',
     'value' => 'no',
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'nav_radius',
     'value' => '18px',
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'nav_shadow_style',
     'value' => 'soft',
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'global_font',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'head_codes',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'theme',
     'key' => 'foot_codes',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'safe',
     'key' => 'password_key',
     'value' => 'mxz_',
  ),
  array(
     'module' => 'Main',
     'type' => 'editor',
     'key' => 'editor_driver',
     'value' => 'Editor',
  ),
  array(
     'module' => 'Main',
     'type' => 'seo',
     'key' => 'seo_title',
     'value' => '{{model_name}} 列表',
  ),
  array(
     'module' => 'Main',
     'type' => 'seo',
     'key' => 'seo_keywords',
     'value' => '{{model_name}}',
  ),
  array(
     'module' => 'Main',
     'type' => 'seo',
     'key' => 'seo_website_desc',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'seo',
     'key' => 'seo_limit_domain',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'seo',
     'key' => 'seo_bot_keywords',
     'value' => 'bot
crawl
spider',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_name',
     'value' => '梦小记CMS',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_keys',
     'value' => '梦小记CMS,cms,免费cms,开源cms,简单cms',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_desc',
     'value' => '梦小记CMS',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_open_reg',
     'value' => '1',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_reg_rqstd',
     'value' => 'phone,email',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_reg_fields',
     'value' => 'phone,email',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_reg_required',
     'value' => 'phone,email',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'open_captcha',
     'value' => '1',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'multilingual',
     'value' => '1',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_status',
     'value' => '1',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_status_when',
     'value' => '<h2>网站维护中...</h2>
<h4>为了让您更好的使用系统，我们正在对服务器进行升级，升级期间暂时无法访问。</h4>
<h4>给您带来的不便，敬请谅解！</h4>',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'admin_page_count',
     'value' => '10',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'weblogo',
     'value' => 'website/logo.png',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'webicon',
     'value' => 'website/webicon.ico',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_open_login',
     'value' => '1',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_icp',
     'value' => '',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_copyright',
     'value' => '',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'use_of_cloud',
     'value' => '1',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'website_reg_agreement',
     'value' => '',
  ),
  array(
     'module' => 'Main',
     'type' => 'captcha',
     'key' => 'captcha_driver',
     'value' => 'System',
  ),
  array(
     'module' => 'Main',
     'type' => 'seo',
     'key' => 'seo_title_detail',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'seo',
     'key' => 'seo_keywords_detail',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'seo',
     'key' => 'seo_website_desc_detail',
     'value' => NULL,
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'default_language',
     'value' => 'zh-CN',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'member_weblogo',
     'value' => 'website/member_logo.png',
  ),
  array(
     'module' => 'Main',
     'type' => 'website',
     'key' => 'moduleHomeLock',
     'value' => NULL,
  ),
);

        foreach ($rows as $row) {
            \DB::table('module_system_settings')->updateOrInsert([
                'module' => $row['module'],
                'type' => $row['type'],
                'key' => $row['key'],
            ], $row);
        }

        // 驱动来源类设置：只补充缺失的键，绝不覆盖站点已经选好的驱动
        $driverDefaults = array(
            array(
                'module' => 'Main',
                'type' => 'login',
                'key' => 'login_driver',
                'value' => '',
            ),
            array(
                'module' => 'Main',
                'type' => 'map',
                'key' => 'map_driver',
                'value' => '',
            ),
        );

        foreach ($driverDefaults as $row) {
            $exists = \DB::table('module_system_settings')
                ->where('module', $row['module'])
                ->where('key', $row['key'])
                ->exists();

            if (!$exists) {
                \DB::table('module_system_settings')->insert($row);
            }
        }


    }
}
