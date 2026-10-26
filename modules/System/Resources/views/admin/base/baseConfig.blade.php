@include(moduleAdminTemplate($moduleName)."public.header")
@php
    $currentType = $currentType ?? (int) request('type', 0);
@endphp
<style>
    input[type="radio"] {
        margin: 8px 10px 0px;
    }


    .h-span-val {
        font-size: 20px;
    }

    .h-clear-both {
        clear: both;
    }

    .h-mb-x {
        margin-bottom: 80px;
    }

    .h-red {
        color: red;
        font-size: 17px;
    }

    .h-mb-20 {
        margin-bottom: 20px;
    }

    .h-del-btn {
        color: red;
        margin-top: 11px;
        cursor: pointer;
    }

    .system-config-hero {
        margin-bottom: 20px;
        padding: 22px 24px;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
    }

    .system-config-hero__title {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
    }

    .system-config-hero__desc {
        margin: 10px 0 0;
        color: #64748b;
        line-height: 1.8;
    }

    .system-config-overview {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 18px;
    }

    .system-config-overview__item {
        padding: 16px 18px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.92);
    }

    .system-config-overview__label {
        margin: 0 0 8px;
        color: #64748b;
        font-size: 12px;
    }

    .system-config-overview__value {
        margin: 0;
        color: #0f172a;
        font-size: 20px;
        font-weight: 700;
        line-height: 1.3;
        word-break: break-word;
    }

    .system-config-overview__desc {
        margin-top: 8px;
        color: #94a3b8;
        line-height: 1.7;
    }

    .system-config-panel {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 10px 32px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .system-config-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 0;
        padding: 0;
        list-style: none;
        border-bottom: 0;
    }

    .system-config-tabs > li {
        float: none;
        margin: 0;
    }

    .system-config-tabs > li > a {
        display: block;
        margin: 0;
        min-width: 168px;
        padding: 12px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        color: #334155;
    }

    .system-config-tabs > li.active > a,
    .system-config-tabs > li.active > a:hover,
    .system-config-tabs > li.active > a:focus {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08);
    }

    .system-config-tabs__title {
        display: block;
        font-size: 14px;
        font-weight: 700;
    }

    .system-config-tabs__desc {
        display: block;
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
    }

    .system-config-tabs > li.active > a .system-config-tabs__desc {
        color: #3b82f6;
    }

    .setting-driver-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 12px;
    }

    .setting-driver-option {
        position: relative;
        display: block;
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        cursor: pointer;
        transition: all .2s ease;
    }

    .setting-driver-option:hover {
        border-color: #bfdbfe;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08);
    }

    .setting-driver-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        margin: 0;
    }

    .setting-driver-option.is-active {
        border-color: #bfdbfe;
        background: #eff6ff;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08);
    }

    .setting-driver-option__title {
        display: block;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }

    .setting-driver-option__desc {
        display: block;
        margin-top: 6px;
        color: #64748b;
        line-height: 1.7;
        white-space: normal;
        word-break: break-word;
    }

    .setting-driver-empty {
        padding: 18px 20px;
        margin-top: 12px;
        border: 1px dashed #d7dde5;
        border-radius: 14px;
        background: #fbfcfe;
        color: #64748b;
        font-size: 13px;
        line-height: 1.8;
    }

    .setting-driver-empty a {
        color: #2563eb;
    }

    @media (max-width: 991px) {
        .system-config-overview,
        .setting-driver-grid {
            grid-template-columns: 1fr;
        }

        .system-config-tabs > li,
        .system-config-tabs > li > a {
            width: 100%;
        }
    }
</style>
<body>
@include(moduleAdminTemplate($moduleName)."public.nav")

<div class="page-container">

    <!-- Page content -->
    <div class="page-content">

    @include(moduleAdminTemplate($moduleName)."public.left")

    <!-- Main content -->
        <div class="content-wrapper">

            <!-- Page header -->
            <div class="page-header">
                @include(moduleAdminTemplate($moduleName)."public.page", ['breadcrumb'=>['系统设置','基本配置']])
            </div>
            <!-- /page header -->


            <!-- Content area -->
            <div class="content" style="margin-top: 1rem;">

                <div class="system-config-hero">
                    <h3 class="system-config-hero__title">系统基础设置</h3>
                    <p class="system-config-hero__desc">这里集中管理网站基础信息、登录注册、邮件短信、编辑器、支付和地图等系统级设置。先确认当前驱动来源，再按分类进入对应配置。</p>
                    <div class="system-config-overview">
                        @foreach($settingOverview as $overview)
                            <div class="system-config-overview__item">
                                <p class="system-config-overview__label">{{$overview['name']}}</p>
                                <p class="system-config-overview__value">{{$overview['value']}}</p>
                                <div class="system-config-overview__desc">{{$overview['desc']}}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="panel panel-flat system-config-panel">
                    <div class="panel-heading">


                        {{csrf_field()}}
                        <fieldset class="content-group">
                            <ul class="nav nav-tabs system-config-tabs">
                                @foreach($settingTabs as $tab)
                                    <li @if($currentType == $tab['type']) class="active" @endif>
                                        <a href="{{moduleAdminJump($moduleName,'base/baseConfig?type=' . $tab['type'])}}">
                                            <span class="system-config-tabs__title">{{$tab['name']}}</span>
                                            <span class="system-config-tabs__desc">{{$tab['desc']}}</span>
                                        </a>
                                    </li>
                                @endforeach
                                {{--                                <li @if($_GET['type']==8) class="active" @endif >--}}
                                {{--                                    <a href data-toggle="tab" data-target="#push">--}}
                                {{--                                        消息推送配置--}}
                                {{--                                    </a>--}}
                                {{--                                </li>--}}
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane @if($currentType==0) active @endif " id="web">
                                    <form id="web_form" class="h-mt20" role="form" method="post">
                                        {{csrf_field()}}
                                        <legend class="text-bold mt-20">网站基本配置</legend>

                                        <div class="col-md-12">
                                            <label>
                                                网站名称
                                            </label>
                                            <input type="text" class="form-control" name="base_name"
                                                   value="{{cacheGlobalSettingsByKey('base_name')}}">
                                        </div>
                                        <div class="col-md-4 mt-20">
                                            <label>
                                                网站logo
                                            </label>
                                            <div>
                                                <div class="fileinput-new-div col-lg-11" data-provides="fileinput"
                                                     style="padding-left: 0">
                                                    <div class="fileinput-preview" data-trigger="fileinput"
                                                         style="width: 240px;height: 50px">
                                                        @if(cacheGlobalSettingsByKey('weblogo'))
                                                            <img id="addImg" class="img-fluid "
                                                                 style="width: 240px;height: 50px"
                                                                 src="{{GetLocalFileByPath(cacheGlobalSettingsByKey('weblogo'))}}"
                                                                 alt=""/>
                                                        @endif
                                                    </div>
                                                    <span class="btn btn-primary  btn-file">
                                                    <span class="fileinput-new">选择</span>
                                                    <span class="fileinput-exists">更换</span>
                                                        <input type="file" id="images" name="weblogo"
                                                               onchange="showFile('images','addImg')"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mt-20">
                                            <label>
                                                会员中心logo
                                            </label>
                                            <div>
                                                <div class="fileinput-new-div col-lg-11" data-provides="fileinput"
                                                     style="padding-left: 0">
                                                    <div class="fileinput-preview" data-trigger="fileinput"
                                                         style="width: 240px;height: 50px">
                                                        <img id="addImg2" class="img-fluid "
                                                             style="width: 239px;height: 49px"
                                                             src="{{GetLocalFileByPath(cacheGlobalSettingsByKey('member_weblogo'))}}"
                                                             alt=""/>
                                                    </div>
                                                    <span class="btn btn-primary  btn-file">
                                                    <span class="fileinput-new">选择</span>
                                                    <span class="fileinput-exists">更换</span>
                                                        <input type="file" id="member_weblogo" name="member_weblogo"
                                                               onchange="showFile('member_weblogo','addImg2')"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mt-20">
                                            <label>
                                                地址栏ico
                                            </label>
                                            <div>
                                                <div class="fileinput-new-div col-lg-6" data-provides="fileinput"
                                                     style="padding-left: 0">
                                                    <div class="fileinput-preview" data-trigger="fileinput"
                                                         style="width: 35px;height: 35px">
                                                        @if(cacheGlobalSettingsByKey('webicon'))
                                                            <img id="addIco" class="img-fluid "
                                                                 style="width: 32px;height: 32px"
                                                                 src="{{GetLocalFileByPath(cacheGlobalSettingsByKey('webicon'))}}"
                                                                 alt=""/>
                                                        @endif
                                                    </div>
                                                    <span class="btn btn-primary  btn-file">
                                                    <span class="fileinput-new">选择</span>
                                                    <span class="fileinput-exists">更换</span>
                                                        <input type="file" id="ico" name="webicon"
                                                               onchange="showFile('ico','addIco')"></span>
                                                </div>
                                                <div class="fileinput-new-div col-lg-6">
                                                    <p class="text-muted">
                                                        <br>
                                                        建议尺寸 64 * 64 (像素)的.ico文件。 <br>
                                                    </p>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <label>
                                                SEO名称
                                            </label>
                                            <input type="text" class="form-control" name="website_name"
                                                   value="{{cacheGlobalSettingsByKey('website_name')}}">
                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <label>
                                                关键字
                                            </label>
                                            <input type="text" class="form-control" name="website_keys"
                                                   value="{{cacheGlobalSettingsByKey('website_keys')}}"
                                                   placeholder="关键词，不要超过100个字符！">
                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <label>
                                                网站描述
                                            </label>
                                            <textarea class="form-control" placeholder="请填写描述，不要超过200个字符！"
                                                      name="website_desc" class="form-text" rows="4"
                                                      style="height: 100px;">{{cacheGlobalSettingsByKey('website_desc')}}</textarea>
                                        </div>
                                        <div class="col-md-6 mt-20">
                                            <label>
                                                备案号
                                            </label>
                                            <input type="text" class="form-control" name="website_icp"
                                                   value="{{cacheGlobalSettingsByKey('website_icp')}}">
                                        </div>
                                        <div class="col-md-6 mt-20">
                                            <label>
                                                版权声明
                                            </label>
                                            <input type="text" class="form-control" name="website_copyright"
                                                   value="{{cacheGlobalSettingsByKey('website_copyright')}}">
                                        </div>
                                        <div class="h-clear-both"></div>
                                        <legend class="text-bold mt-20">前台登录/注册</legend>
                                        <div class="col-md-12">
                                            <label>
                                                会员登录
                                            </label>
                                            <br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="1" name="website_open_login"
                                                       @if(cacheGlobalSettingsByKey('website_open_login')==1) checked @endif >
                                                <span class="h-span-val">开启</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="0" name="website_open_login"
                                                       @if(cacheGlobalSettingsByKey('website_open_login')==0) checked @endif >
                                                <span class="h-span-val">关闭</span>
                                            </label>

                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>
                                                会员注册
                                            </label>
                                            <br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="1" name="website_open_reg"
                                                       @if(cacheGlobalSettingsByKey('website_open_reg')==1) checked @endif >
                                                <span class="h-span-val">开启</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="0" name="website_open_reg"
                                                       @if(cacheGlobalSettingsByKey('website_open_reg')==0) checked @endif >
                                                <span class="h-span-val">关闭</span>
                                            </label>

                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>
                                                注册验证
                                            </label>
                                            <br>
                                            <label class="checkbox-inline">
                                                <input type="checkbox" class="styled h-radio"
                                                       value="phone" name="website_reg_rqstd[]"
                                                       @if(in_array('phone',explode(",", cacheGlobalSettingsByKey('website_reg_rqstd')))) checked @endif>
                                                <span class="h-span-val">手机号码</span>
                                            </label>

                                            <label class="checkbox-inline">
                                                <input type="checkbox" class="styled h-radio"
                                                       value="email" name="website_reg_rqstd[]"
                                                       @if(in_array('email',explode(",", cacheGlobalSettingsByKey('website_reg_rqstd')))) checked @endif >
                                                <span class="h-span-val">电子邮件</span>
                                            </label>
                                            <span class="help-block">验证字段，都会发送对应的验证码，请确认配置是否完全</span>
                                        </div>

                                        @php
                                            $websiteRegFieldsRaw = trim((string) __E('website_reg_fields'));
                                            $websiteRegRequiredRaw = trim((string) __E('website_reg_required'));
                                            $websiteRegFields = $websiteRegFieldsRaw === '' ? [] : explode(",", $websiteRegFieldsRaw);
                                            $websiteRegRequired = $websiteRegRequiredRaw === '' ? [] : explode(",", $websiteRegRequiredRaw);
                                        @endphp

                                        <div class="col-md-12 mt-20">
                                            <label>
                                                注册字段显示
                                            </label>
                                            <br>
                                            <label class="checkbox-inline">
                                                <input type="checkbox" class="styled h-radio"
                                                       value="nickname" name="website_reg_fields[]"
                                                       @if(in_array('nickname',$websiteRegFields)) checked @endif>
                                                <span class="h-span-val">昵称</span>
                                            </label>
                                            <label class="checkbox-inline">
                                                <input type="checkbox" class="styled h-radio"
                                                       value="phone" name="website_reg_fields[]"
                                                       @if(in_array('phone',$websiteRegFields)) checked @endif>
                                                <span class="h-span-val">手机号码</span>
                                            </label>
                                            <label class="checkbox-inline">
                                                <input type="checkbox" class="styled h-radio"
                                                       value="email" name="website_reg_fields[]"
                                                       @if(in_array('email',$websiteRegFields)) checked @endif>
                                                <span class="h-span-val">电子邮件</span>
                                            </label>
                                            <span class="help-block">用户名、密码、确认密码固定显示；邮箱/手机如开启验证码，会自动在前台显示。</span>
                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>
                                                注册字段必填
                                            </label>
                                            <br>
                                            <label class="checkbox-inline">
                                                <input type="checkbox" class="styled h-radio"
                                                       value="nickname" name="website_reg_required[]"
                                                       @if(in_array('nickname',$websiteRegRequired)) checked @endif>
                                                <span class="h-span-val">昵称</span>
                                            </label>
                                            <label class="checkbox-inline">
                                                <input type="checkbox" class="styled h-radio"
                                                       value="phone" name="website_reg_required[]"
                                                       @if(in_array('phone',$websiteRegRequired)) checked @endif>
                                                <span class="h-span-val">手机号码</span>
                                            </label>
                                            <label class="checkbox-inline">
                                                <input type="checkbox" class="styled h-radio"
                                                       value="email" name="website_reg_required[]"
                                                       @if(in_array('email',$websiteRegRequired)) checked @endif>
                                                <span class="h-span-val">电子邮件</span>
                                            </label>
                                            <span class="help-block">如果启用了邮箱/手机验证码，对应字段会自动按必填处理。</span>
                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>
                                                注册协议【<a target="_blank"
                                                        href="{{moduleAdminJump('Formtools','model?moduleName=Formtools&action=List&model=agreement')}}">协议列表</a>】
                                            </label>
                                            <br>
                                            @if(!empty($agreementList))
                                                @foreach($agreementList as $agree)
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" class="styled h-radio"
                                                               value="{{$agree['id']}}" name="website_reg_agreement[]"
                                                               @if(in_array($agree['id'],explode(",", cacheGlobalSettingsByKey('website_reg_agreement')))) checked @endif>
                                                        <span class="h-span-val">{{$agree['name']}}</span>
                                                    </label>
                                                @endforeach
                                            @else
                                                <span class="help-block">当前还没有可关联协议，请先到协议列表新增协议内容。</span>
                                            @endif
                                            <span class="help-block">勾选后会同步到前台注册页，用户必须勾选后才能完成注册，详情页地址来自协议模型。</span>
                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>
                                                提交验证码
                                            </label>
                                            <br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="1" name="open_captcha"
                                                       @if(cacheGlobalSettingsByKey('open_captcha')==1) checked @endif >
                                                <span class="h-span-val">开启</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="0" name="open_captcha"
                                                       @if(cacheGlobalSettingsByKey('open_captcha')==0) checked @endif >
                                                <span class="h-span-val">关闭</span>
                                            </label>
                                            <span class="help-block">验证码开启需要安装对应的验证码插件</span>
                                        </div>

                                        <div class="h-clear-both"></div>
                                        <legend class="text-bold mt-20">其他</legend>
                                        {{--<div class="col-md-12 mt-20">
                                            <label>
                                                开启多货币
                                            </label>
                                            <br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="1" name="multi_currency"
                                                       @if(cacheGlobalSettingsByKey('multi_currency')==1) checked @endif >
                                                <span class="h-span-val">开启</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="0" name="multi_currency"
                                                       @if(cacheGlobalSettingsByKey('multi_currency')==0) checked @endif >
                                                <span class="h-span-val">关闭</span>
                                            </label>

                                        </div>


                                        <div class="col-md-12 mt-20">
                                            <label>
                                                默认货币
                                            </label>

                                            <select name="default_currency" class="form-control">
                                                @foreach($currencys as $currency)
                                                    <option value="{{$currency['id']}}"
                                                            @if(cacheGlobalSettingsByKey('default_currency') ==$currency['id']) selected @endif>{{$currency["name"]}}</option>
                                                @endforeach
                                            </select>
                                        </div>--}}

                                        <div class="col-md-12">
                                            <label>
                                                开启多语言
                                            </label>
                                            <br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="1" name="multilingual"
                                                       @if(cacheGlobalSettingsByKey('multilingual')==1) checked @endif >
                                                <span class="h-span-val">开启</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="0" name="multilingual"
                                                       @if(cacheGlobalSettingsByKey('multilingual')==0) checked @endif >
                                                <span class="h-span-val">关闭</span>
                                            </label>

                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>
                                                默认语言
                                            </label>

                                            @php
                                                $defaultLanguage = cacheGlobalSettingsByKey('default_language') ?: \App\Support\I18n\ThemeTranslator::DEFAULT_LOCALE;
                                            @endphp
                                            <select name="default_language" class="form-control">
                                                @foreach(\Modules\Main\Services\ServiceModel::getLangList() as $kl=>$vl)
                                                    <option value="{{$kl}}" @if($defaultLanguage === $kl) selected @endif>{{$vl}}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="col-md-6 mt-20">
                                            <label>PC端维护</label><br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="1" name="website_pc_status"
                                                       @if(($maintenanceConfig['pc_status'] ?? 1) == 1) checked @endif >
                                                <span class="h-span-val">正常</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="0" name="website_pc_status"
                                                       @if(($maintenanceConfig['pc_status'] ?? 1) == 0) checked @endif >
                                                <span class="h-span-val">维护中</span>
                                            </label>
                                            <span class="help-block">作用于首页、列表、详情、登录、注册、找回密码等前台页面。</span>
                                        </div>

                                        <div class="col-md-6 mt-20">
                                            <label>API端维护</label><br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="1" name="website_api_status"
                                                       @if(($maintenanceConfig['api_status'] ?? 1) == 1) checked @endif >
                                                <span class="h-span-val">正常</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="0" name="website_api_status"
                                                       @if(($maintenanceConfig['api_status'] ?? 1) == 0) checked @endif >
                                                <span class="h-span-val">维护中</span>
                                            </label>
                                            <span class="help-block">作用于 `/api/page`、`/api/list`、`/api/detail`、`/api/handle`、`/api/anyCall` 等接口。</span>
                                        </div>

                                        <div class="col-md-6 mt-20">
                                            <label>
                                                PC端维护文案
                                            </label>
                                            <textarea class="form-control" name="website_pc_status_when" rows="4"
                                                      style="height: 100px;">{{$maintenanceConfig['pc_message'] ?? ''}}</textarea>
                                        </div>

                                        <div class="col-md-6 mt-20">
                                            <label>
                                                API端维护返回信息
                                            </label>
                                            <textarea class="form-control" name="website_api_status_when" rows="4"
                                                      style="height: 100px;">{{$maintenanceConfig['api_message'] ?? ''}}</textarea>
                                        </div>


                                        <div class="h-clear-both"></div>
                                        <br>
                                        <legend class="text-bold mt-20">后台相关</legend>

                                        <div class="col-md-12">
                                            <label>是否开启云平台</label><br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="1" name="use_of_cloud"
                                                       @if(cacheGlobalSettingsByKey('use_of_cloud')==1) checked @endif >
                                                <span class="h-span-val">开启</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="0" name="use_of_cloud"
                                                       @if(cacheGlobalSettingsByKey('use_of_cloud')==0) checked @endif >
                                                <span class="h-span-val">关闭</span>
                                            </label>
                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>调式模式</label><br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="true" name="website_debug"
                                                       @if(env('APP_DEBUG') == true) checked @endif >
                                                <span class="h-span-val">开启</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="false" name="website_debug"
                                                       @if(env('APP_DEBUG') == false) checked @endif >
                                                <span class="h-span-val">关闭</span>
                                            </label>
                                            <div class="col-sm-12">
                                                <p style="padding-top: 10px;">
                                                    将显示错误信息到html上
                                                </p>
                                            </div>
                                        </div>


                                        <div class="col-md-12 mt-20">
                                            <label>日志模式</label><br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="APP_LOG" value="single"
                                                       @if(env('APP_LOG') =='single') checked @endif >
                                                <span class="h-span-val">single(单文件)</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="APP_LOG" value="daily"
                                                       @if(env('APP_LOG') =='daily') checked @endif >
                                                <span class="h-span-val">daily(日期模式)</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="APP_LOG" value="syslog"
                                                       @if(env('APP_LOG') =='syslog') checked @endif >
                                                <span class="h-span-val">syslog(记录到syslog中)</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="APP_LOG" value="errorlog"
                                                       @if(env('APP_LOG') =='errorlog') checked @endif >
                                                <span class="h-span-val">errorlog(记录到PHP的error_log中)</span>
                                            </label>

                                        </div>


                                        <div class="col-md-12 mt-20">
                                            <label>日志级别</label><br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="LOG_LEVEL" value="debug"
                                                       @if(env('LOG_LEVEL') =='debug') checked @endif >
                                                <span class="h-span-val">debug</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="LOG_LEVEL" value="info"
                                                       @if(env('LOG_LEVEL') =='info') checked @endif >
                                                <span class="h-span-val">info</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="LOG_LEVEL" value="notice"
                                                       @if(env('LOG_LEVEL') =='notice') checked @endif >
                                                <span class="h-span-val">notice</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="LOG_LEVEL" value="warning"
                                                       @if(env('LOG_LEVEL') =='warning') checked @endif >
                                                <span class="h-span-val">warning</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="LOG_LEVEL" value="error"
                                                       @if(env('LOG_LEVEL') =='error') checked @endif >
                                                <span class="h-span-val">error</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="LOG_LEVEL" value="critical"
                                                       @if(env('LOG_LEVEL') =='critical') checked @endif >
                                                <span class="h-span-val">critical</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="LOG_LEVEL" value="alert"
                                                       @if(env('LOG_LEVEL') =='alert') checked @endif >
                                                <span class="h-span-val">alert</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="LOG_LEVEL" value="emergency"
                                                       @if(env('LOG_LEVEL') =='emergency') checked @endif >
                                                <span class="h-span-val">emergency</span>
                                            </label>

                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>模块首页锁定</label><br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="moduleHomeLock" value="lock"
                                                       @if(cacheGlobalSettingsByKey('moduleHomeLock') =='lock') checked @endif >
                                                <span class="h-span-val">锁定</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       name="moduleHomeLock" value=""
                                                       @if(!cacheGlobalSettingsByKey('moduleHomeLock')) checked @endif >
                                                <span class="h-span-val">不锁定</span>
                                            </label>
                                            <div class="col-sm-12">
                                                <p style="padding-top: 10px;">
                                                    设置模块首页锁定后，将不能设置、取消模块设为首页功能
                                                </p>
                                            </div>


                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>
                                                后台每页显示的数量
                                            </label>
                                            <input type="number" class="form-control"
                                                   name="admin_page_count" placeholder="后台每页显示的数量"
                                                   value="{{cacheGlobalSettingsByKey('admin_page_count')}}">
                                        </div>


                                        {{--<div class="col-md-6 mt-20">
                                            <label>是否开启后台验证码</label>

                                            <div>
                                                <label class="radio-inline">
                                                    <input type="radio" name="is_open_admin_verification_code"
                                                           class="styled h-radio" value="1"
                                                           @if($data['base']['is_open_admin_verification_code']=='1') checked @endif>
                                                    <span class="h-span-val">开启</span>
                                                </label>

                                                <label class="radio-inline">
                                                    <input type="radio" name="is_open_admin_verification_code"
                                                           class="styled h-radio" value="2"
                                                           @if($data['base']['is_open_admin_verification_code']=='2') checked @endif>
                                                    <span class="h-span-val">关闭</span>
                                                </label>
                                            </div>
                                        </div>--}}

                                        <div class="h-clear-both mt-20"></div>
                                        <div class="form-group col-md-10 mt-20">
                                            <button type="button" onclick="formSub('web_form','website',0)"
                                                    class="btn btn-primary {{permissions('base/baseConfigSubmit')}}">
                                                提交
                                            </button>
                                        </div>


                                    </form>
                                </div>
                                <div class="tab-pane @if($currentType==1) active @endif " id="captcha">
                                    <form id="captcha_form" class="h-mt20" role="form" method="post">
                                        {{csrf_field()}}
                                        <div class="col-md-12 mt-20">
                                            <label>提交验证码</label>
                                            @include('system::admin.base._driver-options', [
                                                'gridId' => 'captchaDriverGrid',
                                                'driverKey' => 'captcha_driver',
                                                'driverLabel' => '验证码',
                                                'driverPlugins' => $plugin_captcha_list,
                                                'optionDesc' => '表单与提交校验的验证码将通过这个来源生成和校验。',
                                                'showEmptyOption' => false,
                                            ])

                                        </div>
                                        <div class="h-clear-both"></div>

                                        <div class="h-clear-both mt-20"></div>
                                        <div class="form-group col-md-10 mt-20">
                                            <button type="button" onclick="formSub('captcha_form','captcha',1)"
                                                    class="btn btn-primary {{permissions('base/baseConfigSubmit')}}">
                                                提交
                                            </button>
                                        </div>


                                    </form>

                                </div>
                                <div class="tab-pane @if($currentType==2) active @endif " id="email">
                                    <form id="email_form" class="h-mt20" role="form" method="post">
                                        {{csrf_field()}}
                                        <div class="col-md-12 mt-20">
                                            <label>
                                                发件人（所显示的发件人姓名）
                                            </label>
                                            <input type="text" class="form-control" name="MAIL_FROM_NAME"
                                                   value="{{env('MAIL_FROM_NAME')}}" placeholder="发件人">
                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>
                                                邮箱账号（用于发送邮件的邮箱账号）
                                            </label>
                                            <input type="text" class="form-control" name="MAIL_FROM_ADDRESS"
                                                   value="{{env('MAIL_FROM_ADDRESS')}}" placeholder="邮箱账号">
                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>
                                                邮箱密码（用于发送邮件的邮箱密码）
                                            </label>
                                            <input type="text" class="form-control" name="MAIL_PASSWORD"
                                                   value="{{env('MAIL_PASSWORD')}}" placeholder="邮箱密码">
                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>
                                                SMTP（如QQ邮箱为smtp.qq.com）
                                            </label>
                                            <input type="text" class="form-control" name="MAIL_HOST"
                                                   value="{{env('MAIL_HOST')}}" placeholder="SMTP">
                                        </div>

                                        <div class="col-md-12 mt-20">
                                            <label>
                                                发送端口（用于邮件发送端口（TLS一般为25，SSL一般为465））
                                            </label>
                                            <input type="text" class="form-control" name="MAIL_PORT"
                                                   value="{{env('MAIL_PORT')}}" placeholder="发送端口">
                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <label>
                                                发送方式（默认邮箱服务方式为TLS；如果使用TLS方式25端口无法发送，请尝试使用SSL方式465端口发件）
                                            </label>
                                            <br>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="ssl" name="MAIL_ENCRYPTION"
                                                       @if(env('MAIL_ENCRYPTION')=='ssl') checked @endif >
                                                <span class="h-span-val">SSL服务方式</span>
                                            </label>

                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio"
                                                       value="tls" name="MAIL_ENCRYPTION"
                                                       @if(env('MAIL_ENCRYPTION')=='tls') checked @endif >
                                                <span class="h-span-val">TLS服务方式</span>
                                            </label>

                                        </div>
                                        <div class="col-md-12 mt-20">
                                            <label>
                                                发送测试
                                            </label>
                                            <br>
                                            <div class="col-md-6">
                                                <input placeholder="测试邮箱地址" id="email_adress" class="form-control"
                                                       type="text">
                                            </div>
                                            <div class="col-md-2">
                                                <input class="form-control" type="button" id="email_test_button"
                                                       value="发送">
                                            </div>
                                        </div>

                                        <div class="h-clear-both mt-20"></div>
                                        <div class="form-group col-md-10 mt-20">
                                            <button type="button" onclick="formSub('email_form','email',2)"
                                                    class="btn btn-primary {{permissions('base/baseConfigSubmit')}}">
                                                提交
                                            </button>
                                        </div>


                                    </form>

                                </div>
                                <div class="tab-pane @if($currentType==3) active @endif " id="sms">
                                    <form id="sms_form" class="h-mt20" role="form" method="post">
                                        {{csrf_field()}}
                                        <div class="col-md-12 mt-20">
                                            <label>SMS服务商</label>
                                            @include('system::admin.base._driver-options', [
                                                'gridId' => 'smsDriverGrid',
                                                'driverKey' => 'sms_driver',
                                                'driverLabel' => '短信服务',
                                                'driverPlugins' => $plugin_list,
                                                'optionDesc' => '手机验证码和短信通知将通过选中的服务商发送。',
                                                'emptyValue' => '',
                                                'emptyTitle' => '暂不启用',
                                                'emptyDesc' => '不选择服务商，手机验证码与短信通知功能将不可用。',
                                            ])

                                        </div>
                                        <div class="h-clear-both"></div>

                                        <div class="h-clear-both mt-20"></div>
                                        <div class="form-group col-md-10 mt-20">
                                            <button type="button" onclick="formSub('sms_form','sms',3)"
                                                    class="btn btn-primary {{permissions('base/baseConfigSubmit')}}">
                                                提交
                                            </button>
                                        </div>


                                    </form>

                                </div>
                                <div class="tab-pane @if($currentType==4) active @endif " id="pay">
                                    <form id="pay_form" class="h-mt20" role="form" method="post">
                                        {{csrf_field()}}
                                        <div class="col-md-12 mt-20">
                                            <label>支付</label>
                                            @include('system::admin.base._driver-options', [
                                                'gridId' => 'payDriverGrid',
                                                'driverKey' => 'pay_driver',
                                                'driverLabel' => '支付',
                                                'driverPlugins' => $plugin_pay_list,
                                                'optionDesc' => '下单、支付和退款将通过选中的支付通道处理。',
                                                'emptyValue' => '',
                                                'emptyTitle' => '暂不启用',
                                                'emptyDesc' => '不选择支付通道，站点上的在线支付功能将不可用。',
                                            ])

                                        </div>
                                        <div class="h-clear-both"></div>

                                        <div class="h-clear-both mt-20"></div>
                                        <div class="form-group col-md-10 mt-20">
                                            <button type="button" onclick="formSub('pay_form','pay',4)"
                                                    class="btn btn-primary {{permissions('base/baseConfigSubmit')}}">
                                                提交
                                            </button>
                                        </div>


                                    </form>

                                </div>
                                <div class="tab-pane @if($currentType==5) active @endif " id="login">
                                    <form id="login_form" class="h-mt20" role="form" method="post">
                                        {{csrf_field()}}
                                        <div class="col-md-12 mt-20">
                                            <label>登录</label>
                                            @include('system::admin.base._driver-options', [
                                                'gridId' => 'loginDriverGrid',
                                                'driverKey' => 'login_driver',
                                                'driverLabel' => '第三方登录',
                                                'driverPlugins' => $plugin_login_list,
                                                'optionDesc' => '微信、抖音等第三方账号登录将通过选中的插件处理。',
                                                'emptyValue' => '',
                                                'emptyTitle' => '暂不启用',
                                                'emptyDesc' => '不选择插件，站点只保留账号密码和手机验证码登录。',
                                            ])

                                        </div>
                                        <div class="h-clear-both"></div>

                                        <div class="h-clear-both mt-20"></div>
                                        <div class="form-group col-md-10 mt-20">
                                            <button type="button" onclick="formSub('login_form','login',5)"
                                                    class="btn btn-primary {{permissions('base/baseConfigSubmit')}}">
                                                提交
                                            </button>
                                        </div>


                                    </form>

                                </div>
                                <div class="tab-pane @if($currentType==6) active @endif " id="editor">
                                    <form id="editor_form" class="h-mt20" role="form" method="post">
                                        {{csrf_field()}}
                                        <div class="col-md-12 mt-20">
                                            <label>富文本编辑器</label>
                                            @include('system::admin.base._driver-options', [
                                                'gridId' => 'editorDriverGrid',
                                                'driverKey' => 'editor_driver',
                                                'driverLabel' => '富文本编辑器',
                                                'driverPlugins' => $plugin_editor_list,
                                                'optionDesc' => '后台与表单里的富文本输入将使用选中的编辑器内核。',
                                                'emptyValue' => '',
                                                'emptyTitle' => '暂不启用',
                                                'emptyDesc' => '不选择编辑器插件，富文本输入框将降级为普通文本框。',
                                            ])

                                        </div>
                                        <div class="h-clear-both"></div>

                                        <div class="h-clear-both mt-20"></div>
                                        <div class="form-group col-md-10 mt-20">
                                            <button type="button" onclick="formSub('editor_form','editor',6)"
                                                    class="btn btn-primary {{permissions('base/baseConfigSubmit')}}">
                                                提交
                                            </button>
                                        </div>


                                    </form>

                                </div>
                                <div class="tab-pane @if($currentType==7) active @endif " id="map">
                                    <form id="map_form" class="h-mt20" role="form" method="post">
                                        {{csrf_field()}}
                                        <div class="col-md-12 mt-20">
                                            <label>地图</label>
                                            @include('system::admin.base._driver-options', [
                                                'gridId' => 'mapDriverGrid',
                                                'driverKey' => 'map_driver',
                                                'driverLabel' => '地图',
                                                'driverPlugins' => $plugin_map_list,
                                                'optionDesc' => '地址解析、坐标换算和地图展示将通过选中的服务商处理。',
                                                'emptyValue' => '',
                                                'emptyTitle' => '暂不启用',
                                                'emptyDesc' => '不选择地图服务商，定位与地图相关功能将不可用。',
                                            ])

                                        </div>
                                        <div class="h-clear-both"></div>

                                        <div class="h-clear-both mt-20"></div>
                                        <div class="form-group col-md-10 mt-20">
                                            <button type="button" onclick="formSub('map_form','map',7)"
                                                    class="btn btn-primary {{permissions('base/baseConfigSubmit')}}">
                                                提交
                                            </button>
                                        </div>


                                    </form>

                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <!-- Footer -->
            @include(moduleAdminTemplate($moduleName)."public.footer")
            <!-- /footer -->

            </div>
            <!-- /content area -->

        </div>
        <!-- /main content -->

    </div>
    <!-- /page content -->

</div>

<!-- 						Content End		 						-->
<!-- ============================================================== -->
@include(moduleAdminTemplate($moduleName)."public.js")
<script>
    // 所有驱动来源卡片组统一同步选中态
    function syncDriverCards($grid) {
        $grid.find('.setting-driver-option').removeClass('is-active');
        $grid.find('input:checked').closest('.setting-driver-option').addClass('is-active');
    }

    function formSub(form, tig, type) {
        layer.load(1);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            "method": "post",
            "url": "{{moduleAdminJump($moduleName,'base/baseSubmit?form=')}}" + tig,
            "data": new FormData($('#' + form)[0]),
            "dataType": 'json',
            "cache": false,
            "processData": false,
            "contentType": false,
            "success": function (res) {
                layer.closeAll();
                if (res.status == 200) {
                    layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                        window.location.href = "{{moduleAdminJump($moduleName,'base/baseConfig?type=')}}" + type;
                    });
                } else {
                    layer.msg(res.msg, {icon: 5})
                }
            },
            "error": function (res) {
                console.log(res);
            }
        })
    }

    $('[data-driver-grid]').each(function () {
        var $grid = $(this);
        $grid.on('change', 'input[type="radio"]', function () {
            syncDriverCards($grid);
        });
    });

    //email 测试
    $("#email_test_button").click(function () {
        layer.load(1);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            "method": "post",
            "url": "{{moduleAdminJump($moduleName,'base/baseSubmit?form=test_email')}}",
            "data": {'email_adress': $("#email_adress").val(), "_token": '{{csrf_token()}}'},
            "dataType": 'json',
            // "cache": false,
            // "processData": false,
            // "contentType": false,
            "success": function (res) {
                layer.closeAll();
                if (res.status == 200) {
                    layer.msg(res.msg, {icon: 1, time: 1000});
                } else {
                    layer.msg(res.msg, {icon: 5})
                }
            },
            "error": function (res) {
                layer.closeAll();
                console.log(res);
            }
        })
    });

    $(function () {
        $('[data-driver-grid]').each(function () {
            syncDriverCards($(this));
        });
    });

</script>
</body>
</html>
