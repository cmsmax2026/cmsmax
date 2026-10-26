<?php

namespace Modules\Formtools\Http\Controllers\Admin;

use Modules\Main\Services\ServiceModel;
use Modules\ModulesController;

class SettingController extends ModulesController {

    public function setting() {

        if($this->request->isMethod('post')){
            $all = $this->request->all();

            ServiceModel::SettingInsertOrUpdate('Formtools','setting','openapi_enabled', (int) ($all['openapi_enabled'] ?? 1) === 1 ? '1' : '0');
            ServiceModel::SettingInsertOrUpdate('Formtools','setting','openapi_idempotent_ttl', (string) max(60, (int) ($all['openapi_idempotent_ttl'] ?? 600)));
            ServiceModel::SettingInsertOrUpdate('Formtools','setting','openapi_rate_limit', (string) max(1, (int) ($all['openapi_rate_limit'] ?? 60)));
            ServiceModel::SettingInsertOrUpdate('Formtools','setting','openapi_ip_whitelist', trim((string) ($all['openapi_ip_whitelist'] ?? '')));
            //更新缓存
            cacheGlobalSettings(2);
            return redirect(url('admin/formtools/setting'))->with('pageDataMsg', '保存成功')->with("pageDataStatus",200);
        };

        $pageData = getURIByRoute($this->request);
        $pageData['title'] = "模块设置";
        $pageData['subtitle'] = "开放 API";


        $formtool = FormTool::create();
        $openApiEnabled = cacheGlobalSettingsByKey('openapi_enabled',"Formtools");
        $idempotentTtl = cacheGlobalSettingsByKey('openapi_idempotent_ttl',"Formtools");
        $rateLimit = cacheGlobalSettingsByKey('openapi_rate_limit',"Formtools");
        $formtool->tips('这里统一管理 Formtools 对外开放提交的总开关，可以统一限制提交频率和允许访问的 IP。具体到每个模型的字段说明和密钥，到模型列表里的「开放 API」里配置。');

        $formtool->field("openapi_enabled",'开放 API', $openApiEnabled === '' || $openApiEnabled === null ? '1' : (string) $openApiEnabled)
            ->formtype('openapi_enabled', 'radio')
            ->datas('openapi_enabled', FormTool::optionsFromMap(['1' => '启用', '0' => '停用']))
            ->notes('openapi_enabled',"关闭后，外部就无法再向你的模型提交或读取数据了。");
        $formtool->field("openapi_idempotent_ttl",'幂等保留时长（秒）', $idempotentTtl === '' || $idempotentTtl === null ? '600' : (string) $idempotentTtl)
            ->formtype('openapi_idempotent_ttl', 'number')
            ->notes('openapi_idempotent_ttl',"同一份提交在短时间内重复发来会被拦下，避免重复写入。");
        $formtool->field("openapi_rate_limit",'全局单应用频控（次/分钟）', $rateLimit === '' || $rateLimit === null ? '60' : (string) $rateLimit)
            ->formtype('openapi_rate_limit', 'number')
            ->notes('openapi_rate_limit',"每个应用还能单独设提交频率，实际会以更严格的那个为准。");
        $formtool->field("openapi_ip_whitelist",'全局 IP 白名单',cacheGlobalSettingsByKey('openapi_ip_whitelist',"Formtools"))
            ->formtype('openapi_ip_whitelist', 'textarea')
            ->rows('openapi_ip_whitelist', 5)
            ->placeholder('openapi_ip_whitelist', "127.0.0.1\n192.168.1.0/24")
            ->notes('openapi_ip_whitelist',"不填就是不限制。可以填多个 IP（换行或逗号隔开），也支持 192.168.1.0/24 这种网段写法。");



        $formtool->csrf_field();

        $formtool->formaction(url('admin/formtools/setting'));
        $formtool->actionName("保存");

        return $formtool->formView($pageData);
    }

}
