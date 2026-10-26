@include(moduleAdminTemplate($moduleName)."public.header")
<style>
    .h-form-page { padding: 0; }
    .h-form-page .panel { box-shadow: none; }
    .h-form-page .panel-heading { padding: 0; background: transparent; border-bottom: 0; }
    .h-form-page .content-group { margin-bottom: 0; }

    .h-form-section {
        background: #fff;
        border: 1px solid #e7eaec;
        border-radius: 4px;
        margin-bottom: 16px;
    }
    .h-form-section__head {
        padding: 12px 18px;
        border-bottom: 1px solid #e7eaec;
        background: #fafbfc;
        border-radius: 4px 4px 0 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
    }
    .h-form-section__head h5 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #1c2024;
    }
    .h-form-section__head .h-form-section__toggle {
        font-size: 12px;
        color: #999ea6;
    }
    .h-form-section__body {
        padding: 18px 20px 6px;
    }
    .h-form-section.is-collapsed .h-form-section__body { display: none; }

    .h-form-page .form-group {
        margin-bottom: 16px;
    }
    .h-form-page .control-label {
        padding-top: 8px;
        padding-right: 0;
        font-weight: 500;
        color: #374246;
    }
    .h-form-page .control-label .required { color: #ef4444; margin-left: 4px; }
    .h-form-page .form-control { border-radius: 3px; }
    .h-form-page .help-block {
        margin-top: 4px;
        margin-bottom: 0;
        color: #8a929a;
        font-size: 12px;
    }
    .h-form-page .radio-inline {
        padding-top: 8px;
        padding-right: 18px;
    }
    .h-form-page legend { display: none; }

    .h-form-page__actions {
        padding: 16px 0 4px;
        border-top: 1px dashed #e7eaec;
        margin-top: 8px;
    }

    .h-form-section__sub {
        color: #8a929a;
        font-size: 12px;
        font-weight: normal;
        margin-left: 8px;
    }

    .h-template-hint {
        margin-top: 6px;
        padding: 8px 12px;
        border-radius: 3px;
        font-size: 12px;
        line-height: 1.6;
        display: none;
    }
    .h-template-hint.is-shown { display: block; }
    .h-template-hint--info {
        background: #eef6ff;
        color: #1d4ed8;
        border: 1px solid #bfd9ff;
    }
    .h-template-hint--warn {
        background: #fff5f5;
        color: #b91c1c;
        border: 1px solid #ffd2d2;
    }
    .h-template-hint i { margin-right: 4px; }

    .h-template-group {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }
    .h-template-group > select { flex: 1; min-width: 200px; }
    .h-template-group > input { flex: 1; min-width: 160px; }
</style>
<body>

@include(moduleAdminTemplate($moduleName)."public.nav")

<div class="page-container">

    <div class="page-content">

        @include(moduleAdminTemplate($moduleName)."public.left")

        <div class="content-wrapper">

            <div class="content">
                @include(moduleAdminTemplate($moduleName)."public.crumb",['breadcrumb'=>[$pageData['title'],$pageData['subtitle']]])
                <div class="panel panel-flat h-form-page">
                    <div class="panel-heading">

                        <form class="form-horizontal" action="{{url("admin/formtools/modelAdd")}}" method="post"
                              id="post_form" enctype="multipart/form-data">
                            {{csrf_field()}}

                            <div class="h-form-section">
                                <div class="h-form-section__head">
                                    <h5>{{ getTranslateByKey('ft_menu_section') }} <span class="h-form-section__sub">{{ getTranslateByKey('ft_menu_section_sub') }}</span></h5>
                                </div>
                                <div class="h-form-section__body">
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_first_menu') }}<span class="required">*</span></label>
                                        <div class="col-lg-10">
                                            <input type="text" name="menuname" class="form-control" required>
                                            <span class="help-block">{{ getTranslateByKey('ft_help_first_menu') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_model_name') }}<span class="required">*</span></label>
                                        <div class="col-lg-10">
                                            <input type="text" name="name" class="form-control" required onkeyup="changeRemark(this)">
                                            <span class="help-block">{{ getTranslateByKey('ft_help_model_name') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_menu_icon') }}<span class="required">*</span></label>
                                        <div class="col-lg-10">
                                            <input type="text" name="icon" class="form-control" required placeholder="icon-xxx">
                                            <span class="help-block">{{ getTranslateByKey('ft_help_menu_icon') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="h-form-section">
                                <div class="h-form-section__head">
                                    <h5>{{ getTranslateByKey('ft_model_section') }} <span class="h-form-section__sub">{{ getTranslateByKey('ft_model_section_sub') }}</span></h5>
                                </div>
                                <div class="h-form-section__body">
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_type') }}</label>
                                        <div class="col-lg-10">
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio" value="multi" name="type" checked>
                                                <span class="h-span-val">{{ getTranslateByKey('ft_type_list') }}</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio" value="single" name="type">
                                                <span class="h-span-val">{{ getTranslateByKey('ft_type_single') }}</span>
                                            </label>
                                            <span class="help-block">{{ getTranslateByKey('ft_help_type') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_model_identification') }}<span class="required">*</span></label>
                                        <div class="col-lg-10">
                                            <input type="text" id="identification" name="identification" class="form-control" required>
                                            <span class="help-block">{{ getTranslateByKey('ft_help_model_identification') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_access_identification') }}<span class="required">*</span></label>
                                        <div class="col-lg-10">
                                            <input type="text" name="access_identification" class="form-control" required>
                                            <span class="help-block">{{ getTranslateByKey('ft_help_access_identification') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_remark') }}<span class="required">*</span></label>
                                        <div class="col-lg-10">
                                            <input type="text" name="remark" class="form-control" required>
                                            <span class="help-block">{{ getTranslateByKey('ft_help_remark') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_belong_module') }}</label>
                                        <div class="col-lg-10">
                                            <select id="module" name="module" class="form-control">
                                                <option value="">{{ getTranslateByKey('ft_no_associate') }}</option>
                                                @foreach($pageData['modules'] as $module)
                                                    <option value="{{$module['identification']}}">{{$module['name']}}</option>
                                                @endforeach
                                            </select>
                                            <span class="help-block">{{ getTranslateByKey('ft_help_belong_module') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="h-form-section is-collapsed" data-toggle-section>
                                <div class="h-form-section__head">
                                    <h5>{{ getTranslateByKey('ft_front_settings') }} <span class="h-form-section__sub">{{ getTranslateByKey('ft_front_settings_sub') }}</span></h5>
                                    <span class="h-form-section__toggle">{{ getTranslateByKey('ft_click_expand') }}</span>
                                </div>
                                <div class="h-form-section__body">
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_list_template') }}</label>
                                        <div class="col-lg-10 h-template-group">
                                            <select name="home_config[list_template]" id="hListTpl" class="form-control" data-linkage-role="list-template">
                                                @php $listRules = \Modules\Formtools\Helper\FormFunc::templateRules(); @endphp
                                                @foreach(\Modules\Formtools\Helper\FormFunc::listTemplate() as $groupLabel => $options)
                                                    @if(is_array($options))
                                                        <optgroup label="{{ $groupLabel }}">
                                                            @foreach($options as $key => $value)
                                                                @php
                                                                    $rule = $listRules[$key] ?? null;
                                                                    $tplScene = \Modules\Formtools\Support\FormTemplateCatalog::sceneOf($key);
                                                                @endphp
                                                                <option value="{{$key}}"
                                                                        data-implies-type="{{ $rule['implies_type'] ?? 'any' }}"
                                                                        data-implies-doc="{{ $rule['implies_doc_mode'] ?? 'any' }}"
                                                                        data-incompatible-detail="{{ implode(',', $rule['incompatible_detail'] ?? []) }}"
                                                                        data-path="{{ $tplScene ? 'model/'.$tplScene.'/'.$key.'.blade.php' : 'model/'.$key.'.blade.php' }}"
                                                                        @if($key=='list') selected @endif>{{$value}}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    @else
                                                        <option value="{{$groupLabel}}" data-path="">{{$options}}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <input type="text" value="" name="home_config[custom_list_template]" class="form-control" placeholder="{{ getTranslateByKey('ft_custom_template_name') }}">
                                        </div>
                                        <div class="col-lg-10 col-lg-offset-2">
                                            <div class="h-template-hint h-template-hint--info" data-hint="list-info">
                                                <i class="icon-info22"></i> <span data-hint-text></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_page_num') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="20" name="home_config[page_num]" class="form-control" required>
                                            <span class="help-block">{{ getTranslateByKey('ft_help_page_num') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_page_position') }}</label>
                                        <div class="col-lg-10">
                                            <select name="home_config[list_page_template]" class="form-control">
                                                <option value="center">{{ getTranslateByKey('ft_position_center') }}</option>
                                                <option value="left">{{ getTranslateByKey('ft_position_left') }}</option>
                                                <option value="right">{{ getTranslateByKey('ft_position_right') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_detail_template') }}</label>
                                        <div class="col-lg-10 h-template-group">
                                            <select name="home_config[detail_template]" id="hDetailTpl" class="form-control" data-linkage-role="detail-template">
                                                @php $requiresList = \Modules\Formtools\Helper\FormFunc::detailRequiresListKeys(); @endphp
                                                @foreach(\Modules\Formtools\Helper\FormFunc::detailTemplate() as $groupLabel => $options)
                                                    @if(is_array($options))
                                                        <optgroup label="{{ $groupLabel }}">
                                                            @foreach($options as $key => $value)
                                                                @php $tplScene = \Modules\Formtools\Support\FormTemplateCatalog::sceneOf($key); @endphp
                                                                <option value="{{$key}}"
                                                                        data-requires-list="{{ in_array($key, $requiresList) ? '1' : '0' }}"
                                                                        data-path="{{ $tplScene ? 'model/'.$tplScene.'/'.$key.'.blade.php' : 'model/'.$key.'.blade.php' }}"
                                                                        @if($key=='detail') selected @endif>{{$value}}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    @else
                                                        <option value="{{$groupLabel}}" data-path="">{{$options}}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <input type="text" name="home_config[custom_detail_template]" class="form-control" placeholder="{{ getTranslateByKey('ft_custom_template_name') }}">
                                        </div>
                                        <div class="col-lg-10 col-lg-offset-2">
                                            <div class="h-template-hint h-template-hint--warn" data-hint="detail-warn">
                                                <i class="icon-warning22"></i> <span data-hint-text></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_front_doc_mode') }}</label>
                                        <div class="col-lg-10">
                                            <label class="radio-inline">
                                                <input type="radio" name="home_config[front_doc_mode]" class="styled h-radio" value="off" checked>
                                                <span class="h-span-val">{{ getTranslateByKey('common_close') }}</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="home_config[front_doc_mode]" class="styled h-radio" value="on" id="hFrontDocOn">
                                                <span class="h-span-val">{{ getTranslateByKey('ft_open_wiki') }}</span>
                                            </label>
                                            <span class="help-block">{{ getTranslateByKey('ft_help_front_doc_mode') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group" data-doc-field>
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_doc_workbench_title') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="home_config[front_doc_title]" class="form-control" placeholder="{{ getTranslateByKey('ft_doc_title_placeholder') }}">
                                            <span class="help-block">{{ getTranslateByKey('ft_help_doc_title') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group" data-doc-field>
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_doc_workbench_desc') }}</label>
                                        <div class="col-lg-10">
                                            <textarea name="home_config[front_doc_desc]" class="form-control" rows="3" placeholder="{{ getTranslateByKey('ft_doc_desc_placeholder') }}"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group" data-doc-field>
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_doc_price_mode') }}</label>
                                        <div class="col-lg-10">
                                            <select name="home_config[front_doc_price_mode]" class="form-control">
                                                <option value="inherit">{{ getTranslateByKey('ft_price_inherit') }}</option>
                                                <option value="free_only">{{ getTranslateByKey('ft_free_only') }}</option>
                                                <option value="paid_only">{{ getTranslateByKey('ft_paid_only') }}</option>
                                            </select>
                                            <span class="help-block">{{ getTranslateByKey('ft_help_doc_price_mode') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_detail_page_title') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="" name="home_config[detail_page_title]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_detail_page_desc') }}</label>
                                        <div class="col-lg-10">
                                            <textarea name="home_config[detail_page_describe]" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_detail_bg') }}</label>
                                        <div class="col-lg-10">
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio" checked value="color" name="home_config[detail_page_show_type]">
                                                <span class="h-span-val">{{ getTranslateByKey('ft_pure_color') }}</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio" value="img" name="home_config[detail_page_show_type]">
                                                <span class="h-span-val">{{ getTranslateByKey('common_image') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_bg_color') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="" name="home_config[detail_page_bg_color]" class="form-control" placeholder="#ffffff">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_bg_image') }}</label>
                                        <div class="col-lg-10">
                                            <input type="file" name="detail_page_bg_img" class="file-styled" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_home_display') }}</label>
                                        <div class="col-lg-10">
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio" value="yes" name="show_home_page">
                                                <span class="h-span-val">{{ getTranslateByKey('ft_show') }}</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio" value="no" name="show_home_page" checked>
                                                <span class="h-span-val">{{ getTranslateByKey('ft_not_show') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_home_num') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="" name="home_page_num" class="form-control" placeholder="{{ getTranslateByKey('ft_home_num_placeholder') }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_home_sort') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="0" name="home_page_sort" class="form-control">
                                            <span class="help-block">{{ getTranslateByKey('ft_help_home_sort') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_home_title') }}</label>
                                        <div class="col-lg-3">
                                            <input type="text" value="" name="home_config[home_page_title]" class="form-control">
                                        </div>
                                        <label class="col-lg-1 control-label" style="width:auto;padding-left:0;">{{ getTranslateByKey('ft_font_size') }}</label>
                                        <div class="col-lg-2">
                                            <input type="text" value="" name="home_config[home_page_title_size]" class="form-control">
                                        </div>
                                        <label class="col-lg-1 control-label" style="width:auto;padding-left:0;">{{ getTranslateByKey('ft_color') }}</label>
                                        <div class="col-lg-2">
                                            <input type="text" value="" name="home_config[home_page_title_color]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_home_desc') }}</label>
                                        <div class="col-lg-10">
                                            <textarea name="home_config[home_page_describe]" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_desc_font_size') }}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="" name="home_config[home_page_describe_size]" class="form-control">
                                        </div>
                                        <label class="col-lg-1 control-label" style="width:auto;padding-left:0;">{{ getTranslateByKey('ft_color') }}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="" name="home_config[home_page_describe_color]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_home_bg') }}</label>
                                        <div class="col-lg-10">
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio" checked value="color" name="home_config[show_home_type]">
                                                <span class="h-span-val">{{ getTranslateByKey('ft_pure_color') }}</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" class="styled h-radio" value="img" name="home_config[show_home_type]">
                                                <span class="h-span-val">{{ getTranslateByKey('common_image') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_bg_color') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="" name="home_config[home_page_bg_color]" class="form-control" placeholder="#ffffff">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_bg_image') }}</label>
                                        <div class="col-lg-10">
                                            <input type="file" name="home_page_bg_img" class="file-styled" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="h-form-section is-collapsed" data-toggle-section>
                                <div class="h-form-section__head">
                                    <h5>SEO <span class="h-form-section__sub">{{ getTranslateByKey('ft_seo_section_sub') }}</span></h5>
                                    <span class="h-form-section__toggle">{{ getTranslateByKey('ft_click_expand') }}</span>
                                </div>
                                <div class="h-form-section__body">
                                    <h6 style="margin:4px 0 12px;font-size:12px;color:#8a929a;">{{ getTranslateByKey('ft_list_page') }}</h6>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_seo_title') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="home_seo_config[title]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_seo_keyword') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="home_seo_config[keyword]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_seo_describe') }}</label>
                                        <div class="col-lg-10">
                                            <textarea name="home_seo_config[describe]" rows="3" class="form-control"></textarea>
                                        </div>
                                    </div>
                                    <h6 style="margin:12px 0 12px;font-size:12px;color:#8a929a;">{{ getTranslateByKey('ft_detail_page') }}</h6>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_seo_title') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="home_seo_detail_config[title]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_seo_keyword') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="home_seo_detail_config[keyword]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_seo_describe') }}</label>
                                        <div class="col-lg-10">
                                            <textarea name="home_seo_detail_config[describe]" rows="3" class="form-control"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="h-form-section is-collapsed" data-toggle-section>
                                <div class="h-form-section__head">
                                    <h5>{{ getTranslateByKey('ft_data_source_section') }} <span class="h-form-section__sub">{{ getTranslateByKey('ft_data_source_section_sub') }}</span></h5>
                                    <span class="h-form-section__toggle">{{ getTranslateByKey('ft_click_expand') }}</span>
                                </div>
                                <div class="h-form-section__body">
                                    <div class="form-group">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_source') }}</label>
                                        <div class="col-lg-10">
                                            <label class="radio-inline">
                                                <input type="radio" name="other_config[data_source]" class="styled h-radio" value="local" checked>
                                                <span class="h-span-val">{{ getTranslateByKey('ft_local') }}</span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="other_config[data_source]" class="styled h-radio" value="api">
                                                <span class="h-span-val">API</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group data_source_api" style="display:none;">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_list_api') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="other_config[data_source_api_url]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group data_source_api" style="display:none;">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_detail_api') }}</label>
                                        <div class="col-lg-10">
                                            <input type="text" name="other_config[data_source_api_url_detail]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group data_source_api" style="display:none;">
                                        <label class="col-lg-2 control-label">{{ getTranslateByKey('ft_field_mapping') }}</label>
                                        <div class="col-lg-10">
                                            <textarea class="form-control" name="other_config[data_source_field_mapping]" rows="6"></textarea>
                                            <span class="help-block">{{ getTranslateByKey('ft_help_field_mapping') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="h-form-page__actions">
                                <button type="submit" class="btn btn-sm btn-info" id="post_button">{{ getTranslateByKey('common_save') }}</button>
                                <button type="button" class="btn btn-sm btn-default" data-back-url="{{ moduleAdminJump($moduleName, 'index') }}" id="hBackBtn">{{ getTranslateByKey('common_back') }}</button>
                            </div>

                        </form>
                    </div>
                </div>

                @include(moduleAdminTemplate($moduleName)."public.footer")

            </div>

        </div>

    </div>

</div>

@include(moduleAdminTemplate($moduleName)."public.js")

<script>
    $(function () {
        $(".file-styled").uniform({
            wrapperClass: 'bg-warning',
            fileButtonHtml: '<i class="icon-googleplus5"></i>'
        });

        $('input[name="other_config[data_source]"]').on('change', function () {
            var v = $('input[name="other_config[data_source]"]:checked').val();
            if (v === 'api') {
                $('.data_source_api').show();
            } else {
                $('.data_source_api').hide();
            }
        });

        $('[data-toggle-section] .h-form-section__head').on('click', function () {
            var $section = $(this).closest('[data-toggle-section]');
            $section.toggleClass('is-collapsed');
            var collapsed = $section.hasClass('is-collapsed');
            $section.find('.h-form-section__toggle').text(collapsed ? '{{ getTranslateByKey('ft_click_expand') }}' : '{{ getTranslateByKey('ft_click_collapse') }}');
        });

        $('#hBackBtn').on('click', function () {
            window.location = $(this).data('back-url');
        });

        // 模板联动
        var $listTpl = $('#hListTpl');
        var $detailTpl = $('#hDetailTpl');
        var $listInfoHint = $('[data-hint="list-info"]');
        var $detailWarnHint = $('[data-hint="detail-warn"]');
        var detailRequiresList = ['detailLeftList', 'detailRightList', 'detailBottomList'];
        var onlydetailKey = 'onlydetail';

        function setType(value) {
            $('input[name="type"]').filter(function () { return $(this).val() === value; }).prop('checked', true).trigger('change');
        }
        function getType() {
            var v = $('input[name="type"]:checked').val();
            return v || 'multi';
        }
        function setDocMode(value) {
            $('input[name="home_config[front_doc_mode]"]').filter(function () { return $(this).val() === value; }).prop('checked', true);
            toggleDocFields(value === 'on');
        }
        function getDocMode() {
            return $('input[name="home_config[front_doc_mode]"]:checked').val() || 'off';
        }
        function toggleDocFields(show) {
            $('[data-doc-field]').toggle(show);
        }
        function showHint($el, text) {
            $el.find('[data-hint-text]').text(text);
            $el.addClass('is-shown');
        }
        function hideHint($el) {
            $el.removeClass('is-shown');
        }
        function listRule(key) {
            var $opt = $listTpl.find('option[value="' + key + '"]');
            if (!$opt.length) return null;
            return {
                impliesType: $opt.data('implies-type') || 'any',
                impliesDoc: $opt.data('implies-doc') || 'any',
                incompatibleDetail: ($opt.data('incompatible-detail') || '').toString().split(',').filter(Boolean)
            };
        }
        function detailRequiresListFlag(key) {
            return detailRequiresList.indexOf(key) !== -1;
        }
        function refreshListHint() {
            var key = $listTpl.val();
            var rule = listRule(key);
            if (!rule) { hideHint($listInfoHint); return; }
            var parts = [];
            if (rule.impliesType === 'single') {
                parts.push('{{ getTranslateByKey('ft_js_auto_single') }}');
            } else if (rule.impliesType === 'multi') {
                parts.push('{{ getTranslateByKey('ft_js_auto_multi') }}');
            }
            if (rule.impliesDoc === 'on') {
                parts.push('{{ getTranslateByKey('ft_js_auto_doc') }}');
            }
            if (key === onlydetailKey) {
                parts.push('{{ getTranslateByKey('ft_js_only_detail') }}');
            }
            var path = $listTpl.find('option:selected').data('path');
            if (path) {
                parts.push('{{ getTranslateByKey('ft_js_template_file', 'admin', ['name' => ':PATH:']) }}'.replace(':PATH:', path));
            }
            if (parts.length) {
                showHint($listInfoHint, '{{ getTranslateByKey('ft_js_hint_prefix') }}' + parts.join('；') + '。');
            } else {
                hideHint($listInfoHint);
            }
        }
        function refreshDetailWarn() {
            var listKey = $listTpl.val();
            var detailKey = $detailTpl.val();
            if (!detailKey) { hideHint($detailWarnHint); return; }
            var listRuleObj = listRule(listKey);
            var messages = [];
            if (detailRequiresListFlag(detailKey)) {
                if (listKey === onlydetailKey) {
                    messages.push('{{ getTranslateByKey('ft_js_detail_requires_list', 'admin', ['name' => ':TPL:']) }}'.replace(':TPL:', $listTpl.find('option:selected').text()));
                }
            }
            if (listRuleObj && listRuleObj.incompatibleDetail.indexOf(detailKey) !== -1) {
                messages.push('{{ getTranslateByKey('ft_js_list_detail_incompatible', 'admin', ['name' => ':TPL:']) }}'.replace(':TPL:', $listTpl.find('option:selected').text()));
            }
            if (messages.length) {
                $detailWarnHint.removeClass('h-template-hint--info').addClass('h-template-hint--warn');
                showHint($detailWarnHint, messages.join(' '));
            } else {
                var dpath = $detailTpl.find('option:selected').data('path');
                if (dpath) {
                    $detailWarnHint.removeClass('h-template-hint--warn').addClass('h-template-hint--info');
                    showHint($detailWarnHint, '{{ getTranslateByKey('ft_js_template_file', 'admin', ['name' => ':PATH:']) }}'.replace(':PATH:', dpath));
                } else {
                    hideHint($detailWarnHint);
                }
            }
        }
        function applyListLinkage() {
            var key = $listTpl.val();
            var rule = listRule(key);
            if (!rule) return;
            if (rule.impliesType !== 'any') {
                setType(rule.impliesType);
            }
            if (rule.impliesDoc !== 'any') {
                setDocMode(rule.impliesDoc);
            }
            refreshListHint();
            refreshDetailWarn();
        }
        function applyTypeLinkage() {
            var t = getType();
            if (t === 'single' && $listTpl.val() !== onlydetailKey) {
                var $opt = $listTpl.find('option[value="' + onlydetailKey + '"]');
                if ($opt.length) {
                    $listTpl.val(onlydetailKey).trigger('change');
                    return;
                }
            }
            refreshListHint();
            refreshDetailWarn();
        }
        function applyDocModeLinkage() {
            toggleDocFields(getDocMode() === 'on');
        }
        $listTpl.on('change', applyListLinkage);
        $detailTpl.on('change', refreshDetailWarn);
        $('input[name="type"]').on('change', applyTypeLinkage);
        $('input[name="home_config[front_doc_mode]"]').on('change', applyDocModeLinkage);
        // 初始同步
        applyDocModeLinkage();
        applyTypeLinkage();
        refreshListHint();
        refreshDetailWarn();
    });

    function changeRemark(obj) {
        $('input[name=remark]').val($(obj).val());
    }
</script>
</body>
</html>
