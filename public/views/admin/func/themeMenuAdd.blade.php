@include("admin.public.header")
@include('admin.func.partials.themeMenuWorkspaceStyle')
@php($currentTheme = trim((string) ($_GET['m'] ?? 'default')) ?: 'default')
<body class="horizontal">
@include("admin.public.themeMenuNav")

<div class="row page-header" style="margin-bottom: -15px;">
    <div class="col-lg-6 align-self-center ">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url("admin/index")}}">{{getTranslateByKey("common_home_page")}}</a></li>
            <li class="breadcrumb-item"><a href="{{url("admin/theme")}}">{{getTranslateByKey("theme_list")}}</a></li>
            <li class="breadcrumb-item active">{{getTranslateByKey("menu_add")}}</li>
        </ol>
    </div>
</div>

<section class="main-content mt-20">
    <div class="col-md-12">
        <div class="mx-theme-menu-workspace">
            <div class="mx-theme-menu-hero">
                <div>
                    <h2 class="mx-theme-menu-hero__title">{{getTranslateByKey('main_add_theme_menu')}}</h2>
                    <p class="mx-theme-menu-hero__desc">{{getTranslateByKey('main_add_menu_hero_desc')}}</p>
                </div>
                <div class="mx-theme-menu-hero__meta">
                    <span class="mx-theme-menu-chip"><i class="fa fa-paint-brush"></i> {{getTranslateByKey('main_current_theme')}}{{$currentTheme}}</span>
                    <span class="mx-theme-menu-chip"><i class="fa fa-sitemap"></i> {{getTranslateByKey('main_menu_workbench')}}</span>
                    <a href="{{url('admin/theme/themeMenuList?m='.$_GET['m'])}}" class="btn btn-default btn-sm">{{getTranslateByKey('main_back_menu_list')}}</a>
                </div>
            </div>
            <section class="main-content" style="padding-top: 0;">
                <div class="mx-theme-menu-grid">
                    <div class="mx-theme-menu-main">
                        <div class="mx-theme-menu-card">
                            <div class="mx-theme-menu-card__header">
                                <div>
                                    <h3 class="mx-theme-menu-card__title">{{getTranslateByKey('main_basic_info')}}</h3>
                                    <p class="mx-theme-menu-card__desc">{{getTranslateByKey('main_basic_info_desc')}}</p>
                                </div>
                                <span class="mx-theme-menu-chip"><i class="fa fa-edit"></i> {{getTranslateByKey('main_create')}}</span>
                            </div>
                            <div class="mx-theme-menu-card__body">
                                    <form method="post" action="" id="post_form" enctype="multipart/form-data">
                                        {{csrf_field()}}

                                        <div class="form-group">
                                            <label>
                                                {{getTranslateByKey("parent_menu")}}
                                            </label>
                                            <select name="pid" class="form-control m-b">
                                                <option value="0">{{getTranslateByKey("top_level")}}</option>
                                                @include('admin.func.partials.themeMenuParentOptions', ['menus' => $menuList, 'selectedId' => $draftMenu['pid'], 'excludeId' => 0, 'langList' => $langList])
                                            </select>
                                            <small class="form-text text-muted">{{getTranslateByKey('main_parent_menu_hint')}}</small>
                                        </div>


                                        <div class="form-group ">
                                            <label>
                                                {{getTranslateByKey("position")}}
                                            </label>

                                            <div class="form-inline">
                                                <div class="radio radio-inline radio-inverse">
                                                    <input id="position" name="position" type="radio" value="top"
                                                           @if($draftMenu['position'] === 'top') checked @endif>
                                                    <label for="position">
                                                        {{getTranslateByKey("top_menu")}}
                                                    </label>
                                                </div>

                                                <div class=" radio radio-inline radio-inverse">
                                                    <input id="position1" name="position" type="radio"
                                                           value="bottom" @if($draftMenu['position'] === 'bottom') checked @endif>
                                                    <label for="position1">
                                                        {{getTranslateByKey("bottom_menu")}}
                                                    </label>
                                                </div>

                                                <div class=" radio radio-inline radio-inverse">
                                                    <input id="position2" name="position" type="radio"
                                                           value="footer" @if($draftMenu['position'] === 'footer') checked @endif>
                                                    <label for="position2">
                                                        {{getTranslateByKey("footer_menu")}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label>{{getTranslateByKey('main_lang_scope')}}</label>
                                            <select name="lang" class="form-control form-control-rounded" id="menuLangScope">
                                                @foreach($langList as $langCode => $langName)
                                                    <option value="{{$langCode}}" @if($draftMenu['lang'] === $langCode) selected @endif>{{$langName}}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">{{getTranslateByKey('main_lang_scope_hint')}}</small>
                                        </div>

                                        <div class="form-group">
                                            <label>
                                                {{getTranslateByKey("name")}}
                                            </label>
                                            <input type="text" required name="name" value="{{$draftMenu['name']}}"
                                                   class="form-control form-control-rounded">
                                        </div>

                                        <div class="form-group ">
                                            <label>
                                                {{getTranslateByKey("jump_url")}}
                                            </label>
                                            <input type="text" required name="url" value="{{$draftMenu['url']}}"
                                                   class="form-control form-control-rounded">
                                        </div>

                                        <div class="form-group ">
                                            <label>{{getTranslateByKey('main_open_method')}}</label>
                                            <select name="target" class="form-control form-control-rounded">
                                                <option value="_self" @if($draftMenu['target'] === '_self') selected @endif>{{getTranslateByKey('main_current_window')}}</option>
                                                <option value="_blank" @if($draftMenu['target'] === '_blank') selected @endif>{{getTranslateByKey('main_new_window')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group ">
                                            <label>{{getTranslateByKey('main_menu_source')}}</label>
                                            <select name="menu_type" class="form-control form-control-rounded">
                                                <option value="manual" @if($draftMenu['menu_type'] === 'manual') selected @endif>{{getTranslateByKey('main_manual_menu')}}</option>
                                                <option value="module" @if($draftMenu['menu_type'] === 'module') selected @endif>{{getTranslateByKey('module_menu')}}</option>
                                                <option value="model" @if($draftMenu['menu_type'] === 'model') selected @endif>{{getTranslateByKey('model_menu')}}</option>
                                                <option value="page" @if($draftMenu['menu_type'] === 'page') selected @endif>{{getTranslateByKey('main_page_menu')}}</option>
                                                <option value="search" @if($draftMenu['menu_type'] === 'search') selected @endif>{{getTranslateByKey('search_menu')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group ">
                                            <label>{{getTranslateByKey('main_source_module')}}</label>
                                            <input type="text" name="source_module" value="{{$draftMenu['source_module']}}"
                                                   class="form-control form-control-rounded" placeholder="{{getTranslateByKey('main_source_module_placeholder')}}">
                                        </div>

                                        <div class="form-group ">
                                            <label>{{getTranslateByKey('main_source_value')}}</label>
                                            <input type="text" name="source_value" value="{{$draftMenu['source_value']}}"
                                                   class="form-control form-control-rounded" placeholder="{{getTranslateByKey('main_source_value_placeholder')}}">
                                            <small class="form-text text-muted">{{getTranslateByKey('main_source_value_hint')}}</small>
                                        </div>


                                        <div class="form-group ">
                                            <label>
                                                icon
                                            </label>
                                            <div class="input-group">
                                                <input type="text" name="icon" value="{{$draftMenu['icon']}}"
                                                       class="form-control form-control-rounded">
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-info" id="recommendIconButton">{{getTranslateByKey('main_recommend_icon')}}</button>
                                                </span>
                                            </div>
                                            <small class="form-text text-muted">{{getTranslateByKey('main_icon_hint')}}</small>
                                        </div>
                                        <div class="form-group ">
                                            <label>
                                                {{getTranslateByKey("icon_text")}}
                                            </label>
                                            <input type="text" name="icon_character" value="{{$draftMenu['icon_character']}}"
                                                   class="form-control form-control-rounded">
                                        </div>

                                        <div class="form-group ">
                                            <label>
                                                {{getTranslateByKey("sort_desc")}}
                                            </label>
                                            <input type="text" name="sort" value="{{$draftMenu['sort']}}"
                                                   class="form-control form-control-rounded">
                                        </div>
                                        <div class="form-group ">
                                            <label>
                                                {{getTranslateByKey("common_image")}}
                                            </label>
                                            <div class="fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-preview" data-trigger="fileinput"
                                                     style="width: 50px; height:50px;">
                                                </div>
                                                <span class="btn btn-success  btn-file">
                                                    <span class="fileinput-new">
                                                        {{getTranslateByKey("common_select")}}
                                                    </span>
                                                    <span class="fileinput-exists">
                                                        {{getTranslateByKey("common_change")}}
                                                    </span>
                                                    <input type="file" id="images"
                                                           name="cover">
                                                </span>
                                                <a href="#" class="btn btn-danger fileinput-exists"
                                                   data-dismiss="fileinput">
                                                    {{getTranslateByKey("common_delete")}}
                                                </a>
                                            </div>
                                        </div>
                                        <input type="hidden" name="module" value="Main">
                                        <button type="button" id="postButton"
                                                class="btn btn-primary margin-l-5 mx-sm-3">
                                            {{getTranslateByKey("common_submit")}}
                                        </button>
                                        <button type="button" id="ton" onclick="history.go(-1);"
                                                class="btn btn-default ">
                                            {{getTranslateByKey("common_back")}}
                                        </button>

                                    </form>
                            </div>
                        </div>
                    </div>

                    <div class="mx-theme-menu-side">
                        <div class="mx-theme-menu-card">
                            <div class="mx-theme-menu-card__header">
                                <div>
                                    <h3 class="mx-theme-menu-card__title">{{getTranslateByKey('main_quick_source_build')}}</h3>
                                    <p class="mx-theme-menu-card__desc">{{getTranslateByKey('main_quick_source_desc')}}</p>
                                </div>
                                <span class="mx-theme-menu-chip"><i class="fa fa-magic"></i> {{getTranslateByKey('main_auto_fill')}}</span>
                            </div>
                            <div class="mx-theme-menu-card__body">
                                    <div class="alert alert-info">
                                        {{getTranslateByKey('main_model_entry_alert')}}
                                    </div>
                                    <div class="form-group">
                                        <label>{{getTranslateByKey('main_menu_presets')}}</label>
                                        <select class="form-control m-b" id="menuPresetSelect">
                                            <option value="site_basic">{{getTranslateByKey('main_preset_site_basic')}}</option>
                                            <option value="site_form_entry">{{getTranslateByKey('main_preset_form_entry')}}</option>
                                        </select>
                                        <small class="form-text text-muted">{{getTranslateByKey('main_preset_hint')}}</small>
                                    </div>
                                    <button type="button" id="generatePresetButton"
                                            class="btn btn-warning margin-l-5 mx-sm-3">
                                        {{getTranslateByKey('main_generate_menu')}}
                                    </button>
                                    <div class="form-group">
                                        <label>{{getTranslateByKey('main_model_entry_builder')}}</label>
                                        <select class="form-control m-b" id="modelEntryModel">
                                            @foreach(($modelMenu['models'] ?? []) as $m)
                                                <option value="{{$m['access_identification']}}">
                                                    【{{$moduleArray[$modelMenu['identification']]}}】{{$m['name']}} / {{$m['access_identification']}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{getTranslateByKey('main_page_type')}}</label>
                                        <select class="form-control m-b" id="modelEntryType"></select>
                                    </div>
                                    <button type="button" id="buildModelEntryButton"
                                            class="btn btn-primary margin-l-5 mx-sm-3">
                                        {{getTranslateByKey('main_build_model_entry')}}
                                    </button>

                                    <div class="form-group">
                                        <label>
                                            {{getTranslateByKey("module_menu")}}
                                        </label>
                                        <select class="form-control m-b" id="moduleMenu">
                                            @foreach($moduleMenu as $module)
                                                @foreach($module['menuList'] as $m)
                                                    <option
                                                        value="{{$module['identification']}}__{{$m['name']}}__{{$m['url']}}"
                                                        data-module="{{$module['identification']}}"
                                                        data-name="{{$m['name']}}"
                                                        data-url="{{$m['url']}}"
                                                        data-menu-type="module"
                                                        data-source-module="{{$module['identification']}}"
                                                        data-source-value="{{$m['url']}}"
                                                    >
                                                        【{{$moduleArray[$module['identification']]}}
                                                        】{{$m['name']}}</option>
                                                @endforeach
                                            @endforeach

                                        </select>
                                    </div>
                                    <button type="button" id="addMenuButton"
                                            class="btn btn-success margin-l-5 mx-sm-3">
                                        {{getTranslateByKey("add_to_menu")}}
                                    </button>

                                    <div class="form-group mt-5">
                                        <label>
                                            {{getTranslateByKey("model_menu")}}
                                        </label>
                                        <select class="form-control m-b" id="modelMenu">
                                            @foreach($modelMenu['menuList'] as $m)
                                                @php($sourceValue = trim(str_replace('list/', '', $m['url']), '/'))
                                                <option
                                                    value="{{$modelMenu['identification']}}__{{$m['name']}}__{{$m['url']}}"
                                                    data-module="{{$modelMenu['identification']}}"
                                                    data-name="{{$m['name']}}"
                                                    data-url="{{$m['url']}}"
                                                    data-menu-type="model"
                                                    data-source-module="{{$modelMenu['identification']}}"
                                                    data-source-value="{{$sourceValue}}"
                                                >
                                                    【{{$moduleArray[$modelMenu['identification']]}}
                                                    】{{$m['name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" id="addModelMenuButton"
                                            class="btn btn-success margin-l-5 mx-sm-3">
                                        {{getTranslateByKey("add_to_menu")}}
                                    </button>

                                    <div class="form-group mt-5">
                                        <label>{{getTranslateByKey('main_page_menu')}}</label>
                                        <select class="form-control m-b" id="pageMenu">
                                            @foreach($pageMenu as $page)
                                                @php($pageMenuLabel = '【'.getTranslateByKey('main_page_flag').'】' . $page['name'] . ' / ' . ($page['slug'] ?: getTranslateByKey('main_not_set_path')) . (!$page['status'] ? ' / '.getTranslateByKey('main_stopped') : '') . ($page['is_nav'] ? ' / '.getTranslateByKey('main_nav') : ''))
                                                <option
                                                    value="Formtools__{{$page['name']}}__{{$page['url']}}"
                                                    data-module="Formtools"
                                                    data-name="{{$page['name']}}"
                                                    data-url="{{$page['url']}}"
                                                    data-menu-type="page"
                                                    data-source-module="Formtools"
                                                    data-source-value="{{$page['source_value']}}"
                                                >
                                                    {{$pageMenuLabel}}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">{{getTranslateByKey('main_page_menu_hint')}}</small>
                                    </div>
                                    <button type="button" id="addPageMenuButton"
                                            class="btn btn-success margin-l-5 mx-sm-3">
                                        {{getTranslateByKey('main_add_page_to_menu')}}
                                    </button>
                            </div>
                        </div>
                        <div class="mx-theme-menu-card">
                            <div class="mx-theme-menu-card__header">
                                <div>
                                    <h3 class="mx-theme-menu-card__title">{{getTranslateByKey('main_search_entry_preview')}}</h3>
                                    <p class="mx-theme-menu-card__desc">{{getTranslateByKey('main_search_entry_desc')}}</p>
                                </div>
                                <span class="mx-theme-menu-chip"><i class="fa fa-eye"></i> {{getTranslateByKey('main_preview')}}</span>
                            </div>
                            <div class="mx-theme-menu-card__body">
                                    <div class="form-group">
                                        <label>
                                            {{getTranslateByKey("search_menu")}}
                                        </label>
                                        <select class="form-control m-b" id="menuModule">
                                            @foreach($moduleMenu as $module)
                                                @foreach($module['modelList'] as $m)
                                                    <option value="{{$module['identification']}}__{{$m['table']}}">
                                                        【{{$moduleArray[$module['identification']]}}
                                                        】{{$m['name']}}</option>
                                                @endforeach
                                            @endforeach

                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>
                                            {{getTranslateByKey("menu_title")}}
                                        </label>
                                        <input type="text" id="menuTitle"
                                               class="form-control form-control-rounded">
                                    </div>

                                    <button type="button" id="searchMenuButton"
                                            class="btn btn-info margin-l-5 mx-sm-3">
                                        {{getTranslateByKey("search_menu")}}
                                    </button>

                                    <div class="form-group mt-3">
                                        <label>
                                            {{getTranslateByKey("search_menu_list")}}
                                        </label>
                                        <select class="form-control m-b" id="searchModuleMenuList">
                                        </select>
                                    </div>

                                    <button type="button" id="searchAddMenuButton"
                                            class="btn btn-success margin-l-5 mx-sm-3">
                                        {{getTranslateByKey("add_to_menu")}}
                                    </button>
                                    <div class="mx-theme-menu-preview mt-4">
                                        <div class="mx-theme-menu-preview__head">
                                            <div class="mx-theme-menu-preview__title">
                                                <i class="fa fa-circle-o" id="menuPreviewIcon"></i>
                                                <span id="menuPreviewName">{{getTranslateByKey('main_menu_name_empty')}}</span>
                                            </div>
                                            <span class="mx-theme-menu-chip" id="menuPreviewType">{{getTranslateByKey('main_manual_menu')}}</span>
                                        </div>
                                        <div class="mx-theme-menu-preview__subtitle" id="menuPreviewUrl">#</div>
                                        <div class="mx-theme-menu-preview__meta">
                                            <div class="mx-theme-menu-preview__item">
                                                <span class="mx-theme-menu-preview__label">{{getTranslateByKey('position')}}</span>
                                                <span class="mx-theme-menu-preview__value" id="menuPreviewPosition">{{getTranslateByKey('top_menu')}}</span>
                                            </div>
                                            <div class="mx-theme-menu-preview__item">
                                                <span class="mx-theme-menu-preview__label">{{getTranslateByKey('main_lang_scope')}}</span>
                                                <span class="mx-theme-menu-preview__value" id="menuPreviewLang">{{getTranslateByKey('main_lang_scope_global')}}</span>
                                            </div>
                                            <div class="mx-theme-menu-preview__item">
                                                <span class="mx-theme-menu-preview__label">{{getTranslateByKey('main_open_method')}}</span>
                                                <span class="mx-theme-menu-preview__value" id="menuPreviewTarget">{{getTranslateByKey('main_current_window')}}</span>
                                            </div>
                                            <div class="mx-theme-menu-preview__item">
                                                <span class="mx-theme-menu-preview__label">{{getTranslateByKey('main_source_info')}}</span>
                                                <span class="mx-theme-menu-preview__value" id="menuPreviewSource">manual / -</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mx-theme-menu-tip-list mt-4">
                                        <div class="mx-theme-menu-tip">{{getTranslateByKey('main_tip_parent_filter')}}</div>
                                        <div class="mx-theme-menu-tip">{{getTranslateByKey('main_tip_prefer_model_page')}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('admin.public.footer')
            </section>
        </div>
    </div>
</section>
@include('admin.public.js',['load'=> ["custom"]])
</body>
</html>
<script>
    $(function () {
        const themeMenuAddLang = {
            requesting: @json(getTranslateByKey('requesting')),
            pleaseSelectModule: @json(getTranslateByKey('please_select_module')),
            pleaseEnterTitle: @json(getTranslateByKey('please_enter_title')),
            sourceFilled: @json(getTranslateByKey('main_source_auto_filled')),
            presetDone: @json(getTranslateByKey('main_preset_done')),
            presetFailed: @json(getTranslateByKey('main_preset_failed')),
            menuNameEmpty: @json(getTranslateByKey('main_menu_name_empty')),
            manualMenu: @json(getTranslateByKey('main_manual_menu')),
            moduleMenu: @json(getTranslateByKey('module_menu')),
            modelMenu: @json(getTranslateByKey('model_menu')),
            pageMenu: @json(getTranslateByKey('main_page_menu')),
            searchMenu: @json(getTranslateByKey('search_menu')),
            topMenu: @json(getTranslateByKey('top_menu')),
            bottomMenu: @json(getTranslateByKey('bottom_menu')),
            footerMenu: @json(getTranslateByKey('footer_menu')),
            langScopeGlobal: @json(getTranslateByKey('main_lang_scope_global')),
            newWindow: @json(getTranslateByKey('main_new_window')),
            currentWindow: @json(getTranslateByKey('main_current_window'))
        };
        const modelBuilderData = @json($modelMenu['models'] ?? []);
        function recommendIcon(sourceValue, name) {
            const value = `${sourceValue || ''} ${name || ''}`.toLowerCase();
            if (value.includes('about') || value.includes('关于')) return 'fa fa-building-o';
            if (value.includes('contact') || value.includes('contacts') || value.includes('联系')) return 'fa fa-phone';
            if (value.includes('feedback') || value.includes('留言')) return 'fa fa-commenting-o';
            if (value.includes('agreement') || value.includes('协议')) return 'fa fa-file-text-o';
            if (value.includes('news') || value.includes('资讯') || value.includes('动态')) return 'fa fa-newspaper-o';
            if (value.includes('page:') || value.includes('页面') || value.includes('专题')) return 'fa fa-file-o';
            if (value.includes('milestone') || value.includes('历程')) return 'fa fa-flag-checkered';
            if (value.includes('handle') || value.includes('投稿') || value.includes('提交')) return 'fa fa-edit';
            return 'fa fa-circle-o';
        }
        function applyMenuPreset(option, menuType) {
            if (!option.length) {
                return;
            }
            $('input[name=module]').val(option.data('module') || 'Main');
            $('input[name=name]').val(option.data('name') || '');
            $('input[name=url]').val(option.data('url') || '#');
            $('select[name=menu_type]').val(option.data('menu-type') || menuType || 'manual');
            $('input[name=source_module]').val(option.data('source-module') || '');
            $('input[name=source_value]').val(option.data('source-value') || '');
            $('input[name=icon]').val(recommendIcon(option.data('source-value') || option.data('url') || '', option.data('name') || ''));
            refreshMenuPreview();
        }
        function populateModelEntryTypes(selectedAccess, selectedEntryKey) {
            const model = modelBuilderData.find(item => item.access_identification === selectedAccess);
            let options = '';
            if (!model) {
                $('#modelEntryType').html(options);
                return;
            }
            (model.entries || []).forEach(function (entry, index) {
                const selected = selectedEntryKey ? entry.key === selectedEntryKey : index === 0;
                options += `<option value="${entry.key}" ${selected ? 'selected' : ''}>${entry.label}</option>`;
            });
            $('#modelEntryType').html(options);
        }
        function buildModelEntry() {
            const access = $('#modelEntryModel').val();
            const entryKey = $('#modelEntryType').val();
            const model = modelBuilderData.find(item => item.access_identification === access);
            if (!model) {
                return;
            }
            const entry = (model.entries || []).find(item => item.key === entryKey) || model.entries[0];
            if (!entry) {
                return;
            }
            $('input[name=module]').val('Formtools');
            $('input[name=name]').val(entry.name || model.name);
            $('input[name=url]').val(entry.url || ('list/' + access));
            $('select[name=menu_type]').val('model');
            $('input[name=source_module]').val('Formtools');
            $('input[name=source_value]').val(`${access}:${entry.key}`);
            $('input[name=icon]').val(recommendIcon(`${access}:${entry.key}`, entry.name || model.name));
            $('select[name=pid]').val('0');
        }
        function filterParentOptions() {
            const selectedLang = $('select[name=lang]').val() || '';
            const selectedPosition = $('input[name=position]:checked').val() || 'top';
            const parentSelect = $('select[name=pid]');
            const currentValue = parentSelect.val();
            let keepCurrent = false;
            parentSelect.find('option').each(function (index) {
                if (index === 0) {
                    this.hidden = false;
                    $(this).prop('disabled', false);
                    return;
                }
                const optionLang = ($(this).data('lang') || '').toString();
                const optionPosition = ($(this).data('position') || '').toString();
                const visible = optionLang === selectedLang && optionPosition === selectedPosition;
                this.hidden = !visible;
                $(this).prop('disabled', !visible);
                if (visible && $(this).val() === currentValue) {
                    keepCurrent = true;
                }
            });
            if (!keepCurrent) {
                parentSelect.val('0');
            }
        }
        function refreshMenuPreview() {
            const name = ($('input[name=name]').val() || '').trim() || themeMenuAddLang.menuNameEmpty;
            const url = ($('input[name=url]').val() || '').trim() || '#';
            const icon = ($('input[name=icon]').val() || '').trim() || 'fa fa-circle-o';
            const menuType = $('select[name=menu_type]').val() || 'manual';
            const sourceModule = ($('input[name=source_module]').val() || '').trim() || menuType;
            const sourceValue = ($('input[name=source_value]').val() || '').trim() || '-';
            const position = $('input[name=position]:checked').val() || 'top';
            const langCode = $('select[name=lang]').val() || '';
            const target = $('select[name=target]').val() || '_self';
            const typeMap = {
                manual: themeMenuAddLang.manualMenu,
                module: themeMenuAddLang.moduleMenu,
                model: themeMenuAddLang.modelMenu,
                page: themeMenuAddLang.pageMenu,
                search: themeMenuAddLang.searchMenu
            };
            const positionMap = {
                top: themeMenuAddLang.topMenu,
                bottom: themeMenuAddLang.bottomMenu,
                footer: themeMenuAddLang.footerMenu
            };
            const langLabel = $('#menuLangScope option:selected').text() || themeMenuAddLang.langScopeGlobal;
            const targetLabel = target === '_blank' ? themeMenuAddLang.newWindow : themeMenuAddLang.currentWindow;
            $('#menuPreviewName').text(name);
            $('#menuPreviewUrl').text(url);
            $('#menuPreviewType').text(typeMap[menuType] || menuType);
            $('#menuPreviewPosition').text(positionMap[position] || position);
            $('#menuPreviewLang').text(langCode === '' ? themeMenuAddLang.langScopeGlobal : langLabel);
            $('#menuPreviewTarget').text(targetLabel);
            $('#menuPreviewSource').text(sourceModule + ' / ' + sourceValue);
            $('#menuPreviewIcon').attr('class', icon);
        }
        $("#postButton").click(function () {
            popup({
                type: 'load', msg: themeMenuAddLang.requesting, delay: 800, callBack: function () {

                    $.ajax({
                        "method": "post",
                        "url": "{{url('admin/theme/themeMenuAdd?m='.$_GET['m'])}}",
                        "data": new FormData($('#post_form')[0]),                    //$("#post_form").serialize(),
                        "dataType": 'json',
                        "cache": false,
                        "processData": false,
                        "contentType": false,
                        "success": function (res) {
                            if (res.status == 200) {
                                popup({type: "success", msg: res.msg, delay: 2000});
                                setTimeout(function () {
                                    location.href = "{{url('admin/theme/themeMenuList?m='.$_GET['m'])}}";
                                }, 2000);
                            } else {
                                popup({type: "error", msg: res.msg, delay: 2000});
                            }
                        },
                        "error": function (res) {
                            console.log(res);
                        }
                    })
                }
            });
        })

        $('#addMenuButton').click(function () {
            var option = $('#moduleMenu option:selected');
            if (option.length) {
                applyMenuPreset(option, 'module');
            }
        });

        $('#addModelMenuButton').click(function () {
            var option = $('#modelMenu option:selected');
            if (option.length) {
                applyMenuPreset(option, 'model');
            }
        });
        $('#addPageMenuButton').click(function () {
            var option = $('#pageMenu option:selected');
            if (option.length) {
                applyMenuPreset(option, 'page');
            }
        });
        $('#modelEntryModel').change(function () {
            populateModelEntryTypes($(this).val());
        });
        $('select[name=lang]').change(filterParentOptions);
        $('input[name=position]').change(filterParentOptions);
        $('#post_form').on('input change', 'input, select', function () {
            refreshMenuPreview();
        });
        $('#buildModelEntryButton').click(function () {
            buildModelEntry();
            refreshMenuPreview();
        });
        $('#recommendIconButton').click(function () {
            const sourceValue = $('input[name=source_value]').val() || $('input[name=url]').val();
            const name = $('input[name=name]').val();
            $('input[name=icon]').val(recommendIcon(sourceValue, name));
            refreshMenuPreview();
        });
        $('#generatePresetButton').click(function () {
            popup({
                type: 'load', msg: themeMenuAddLang.requesting, delay: 400, callBack: function () {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': '{{csrf_token()}}'
                        },
                        method: 'post',
                        url: "{{url('admin/theme/themeMenuGeneratePreset?m='.$_GET['m'])}}",
                        dataType: 'json',
                        data: {
                            preset: $('#menuPresetSelect').val(),
                            position: $('input[name=position]:checked').val(),
                            pid: $('select[name=pid]').val() || 0,
                            lang: $('select[name=lang]').val() || ''
                        },
                        success: function (res) {
                            if (res.status == 200) {
                                popup({type: "success", msg: res.msg || themeMenuAddLang.presetDone, delay: 2000});
                            } else {
                                popup({type: "error", msg: res.msg || themeMenuAddLang.presetFailed, delay: 2000});
                            }
                        },
                        error: function (res) {
                            console.log(res);
                        }
                    });
                }
            });
        });

        $('#searchMenuButton').click(function () {
            var table = $('#menuModule').val();
            var title = $('#menuTitle').val();
            if (!table) return layer.msg(themeMenuAddLang.pleaseSelectModule, {icon: 2});
            if (!title) return layer.msg(themeMenuAddLang.pleaseEnterTitle, {icon: 2});
            var arr = table.split('__');
            popup({
                type: 'load', msg: themeMenuAddLang.requesting, delay: 800, callBack: function () {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': '{{csrf_token()}}'
                        },
                        "method": "post",
                        "url": "{{url('admin/theme/themeMenuSearchModuleMenu')}}",
                        "data": {
                            module: arr[0],
                            table: arr[1],
                            title: title
                        },
                        "dataType": 'json',
                        "cache": false,
                        "success": function (res) {
                            if (res.status == 200) {
                                // popup({type: "success", msg: res.msg, delay: 2000});
                                var option = ``;
                                var data = res.data;
                                data.menuList.forEach(function (val) {
                                    option += `<option value="${data.identification}__${val.name}__${val.url}" data-module="${data.identification}" data-name="${val.name}" data-url="${val.url}" data-menu-type="search" data-source-module="${data.identification}" data-source-value="${arr[1]}:${title}">${val.name}</option>`;
                                });
                                $('#searchModuleMenuList').html(option);
                            } else {
                                popup({type: "error", msg: res.msg, delay: 2000});
                            }
                        },
                        "error": function (res) {
                            console.log(res);
                        }
                    })
                }
            });
        });
        $('#searchAddMenuButton').click(function () {
            var option = $('#searchModuleMenuList option:selected');
            if (option.length) {
                applyMenuPreset(option, 'search');
                refreshMenuPreview();
            }
        });
        populateModelEntryTypes($('#modelEntryModel').val());
        filterParentOptions();
        refreshMenuPreview();
    })
</script>
