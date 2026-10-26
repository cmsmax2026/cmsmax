@include("admin.public.header")
<style>
    .mx-theme-shell {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 20px;
    }

    .mx-theme-card {
        margin-bottom: 18px;
        padding: 20px;
        border: 1px solid #e8edf5;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
    }

    .mx-theme-card__title {
        margin: 0 0 8px;
        color: #0f172a;
        font-size: 18px;
        font-weight: 700;
    }

    .mx-theme-card__desc {
        margin: 0 0 18px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.8;
    }

    .mx-theme-preview {
        position: sticky;
        top: 24px;
    }

    .mx-logo-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 220px;
        border-radius: 18px;
        background: radial-gradient(circle at top, #dbeafe 0%, #eff6ff 40%, #ffffff 100%);
        border: 1px dashed #bfdbfe;
        margin-bottom: 18px;
    }

    .mx-logo-preview__item {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 18px;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.85);
        box-shadow: 0 14px 30px rgba(37, 99, 235, 0.14);
        transform-origin: center center;
        will-change: transform, filter;
    }

    .mx-logo-preview__item img {
        max-width: 180px;
        max-height: 84px;
    }

    .mx-logo-preview__item.logo-animated,
    .mx-logo-preview__item.logo-animated-spin {
        animation: mxAdminLogoSpin var(--mx-logo-animation-duration, 6s) linear infinite;
    }

    .mx-logo-preview__item.logo-animated-pulse {
        animation: mxAdminLogoPulse var(--mx-logo-animation-duration, 4s) ease-in-out infinite;
    }

    .mx-logo-preview__item.logo-animated-float {
        animation: mxAdminLogoFloat var(--mx-logo-animation-duration, 3.6s) ease-in-out infinite;
    }

    .mx-logo-preview__item.logo-animated-swing {
        animation: mxAdminLogoSwing var(--mx-logo-animation-duration, 4.5s) ease-in-out infinite;
    }

    .mx-logo-preview__item.logo-animated-glow img {
        animation: mxAdminLogoGlow var(--mx-logo-animation-duration, 3s) ease-in-out infinite;
    }

    .mx-theme-savebar {
        position: sticky;
        bottom: 16px;
        z-index: 20;
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .mx-theme-savebar .btn {
        min-width: 140px;
        height: 44px;
        border-radius: 999px;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.2);
    }

    .mx-color-field {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .mx-color-swatch {
        display: inline-flex;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        border: 1px solid #dbe3ef;
        background: #ffffff;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.45);
        flex: 0 0 auto;
    }

    .mx-color-text {
        min-width: 0;
        flex: 1 1 auto;
    }

    .mx-color-picker {
        width: 52px;
        height: 38px;
        padding: 4px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        background: #fff;
        cursor: pointer;
        flex: 0 0 auto;
    }

    .mx-color-tip {
        margin-top: 8px;
        color: #64748b;
        font-size: 12px;
    }

    @keyframes mxAdminLogoSpin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes mxAdminLogoPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }

    @keyframes mxAdminLogoFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }

    @keyframes mxAdminLogoSwing {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-6deg); }
        75% { transform: rotate(6deg); }
    }

    @keyframes mxAdminLogoGlow {
        0%, 100% { filter: drop-shadow(0 0 0 rgba(96, 165, 250, 0)); }
        50% { filter: drop-shadow(0 0 18px rgba(96, 165, 250, 0.52)); }
    }

    @media (max-width: 991px) {
        .mx-theme-shell {
            grid-template-columns: 1fr;
        }

        .mx-theme-preview {
            position: static;
        }
    }
</style>
<body class="horizontal">

<div class="main-horizontal-nav">
    <nav>
        <!-- Menu Toggle btn-->
        <div class="menu-toggle">
            <h3>{{getTranslateByKey("menu_navigation")}}</h3>
            <button type="button" id="menu-btn">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <ul id="respMenu" class="ace-responsive-menu" data-menu-style="horizontal">
            <li>
                <a href="{{url("admin/theme/setting?m=".$_GET['m'])}}"> <i class="fa fa-arrow-circle-left"></i>{{getTranslateByKey("return_theme_preview")}}</a>
            </li>
        </ul>
    </nav>
</div>

<div class="row page-header" style="margin-bottom: 0px;">
    <div class="col-lg-6 align-self-center ">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url("admin/index")}}">{{getTranslateByKey("common_home_page")}}</a></li>
            <li class="breadcrumb-item"><a href="{{url("admin/theme")}}">{{getTranslateByKey("theme_list")}}</a></li>
            <li class="breadcrumb-item active">{{getTranslateByKey("page_configuration")}}</li>
        </ol>
    </div>
</div>

<section class="container-fluid pl-5 pr-5 ">

    <div class="row pb-5">
        <div class="col-12 bg-light p-3" style="min-height: calc(100vh - 125px)">
            <div class="mx-theme-shell">
                <aside class="mx-theme-preview">
                    <div class="mx-theme-card">
                        <h3 class="mx-theme-card__title">{{getTranslateByKey('main_logo_preview_title')}}</h3>
                        <p class="mx-theme-card__desc">{{getTranslateByKey('main_logo_preview_desc')}}</p>
                        <div class="mx-logo-preview">
                            <div class="mx-logo-preview__item {{cacheGlobalSettingsByKey('logo_animated')}}" id="logoPreviewTarget" style="--mx-logo-animation-duration: {{cacheGlobalSettingsByKey('logo_animation_speed') ?: '6s'}};">
                                <img src="{{GetLocalFileByPath(cacheGlobalSettingsByKey('weblogo'))}}" alt="logo preview">
                            </div>
                        </div>
                        <div class="alert alert-info" style="border-radius: 12px;">
                            {{getTranslateByKey('main_logo_preview_alert')}}
                        </div>
                    </div>
                </aside>

                <div>

                <form method="post" action="" id="diy_form" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <div class="mx-theme-card">
                        <h3 class="mx-theme-card__title">{{getTranslateByKey("logo_animation")}}</h3>
                        <p class="mx-theme-card__desc">{{getTranslateByKey('main_logo_animation_desc')}}</p>
                        <div class="form-group">
                            <div class="fileinput-new" data-provides="fileinput">
                                <div class="fileinput-preview" data-trigger="fileinput" style="border: none;">
                                    <img src="{{GetLocalFileByPath(cacheGlobalSettingsByKey('weblogo'))}}" style="width: auto; height:100px;">
                                </div>
                            </div>
                        </div>

                        <div class="form-group ">
                            <label>{{getTranslateByKey("animation_effect")}}</label>

                            <div class="form-inline">
                                <div class="radio radio-inline radio-success">
                                    <input id="logo_animated" type="radio" name="logo_animated" value="" @if(!cacheGlobalSettingsByKey("logo_animated")) checked @endif>
                                    <label for="logo_animated"> {{getTranslateByKey("no_animation")}} </label>
                                </div>
                                <div class="radio radio-inline radio-success">
                                    <input id="logo_animated2" type="radio" name="logo_animated" value="logo-animated" @if(cacheGlobalSettingsByKey("logo_animated")=="logo-animated") checked @endif>
                                    <label for="logo_animated2"> {{getTranslateByKey("rotate_360")}} </label>
                                </div>
                                <div class="radio radio-inline radio-success">
                                    <input id="logo_animated3" type="radio" name="logo_animated" value="logo-animated-pulse" @if(cacheGlobalSettingsByKey("logo_animated")=="logo-animated-pulse") checked @endif>
                                    <label for="logo_animated3"> {{getTranslateByKey("heartbeat_rhythm")}} </label>
                                </div>
                                <div class="radio radio-inline radio-success">
                                    <input id="logo_animated4" type="radio" name="logo_animated" value="logo-animated-float" @if(cacheGlobalSettingsByKey("logo_animated")=="logo-animated-float") checked @endif>
                                    <label for="logo_animated4"> {{getTranslateByKey('main_logo_animation_float')}} </label>
                                </div>
                                <div class="radio radio-inline radio-success">
                                    <input id="logo_animated5" type="radio" name="logo_animated" value="logo-animated-swing" @if(cacheGlobalSettingsByKey("logo_animated")=="logo-animated-swing") checked @endif>
                                    <label for="logo_animated5"> {{getTranslateByKey('main_logo_animation_swing')}} </label>
                                </div>
                                <div class="radio radio-inline radio-success">
                                    <input id="logo_animated6" type="radio" name="logo_animated" value="logo-animated-glow" @if(cacheGlobalSettingsByKey("logo_animated")=="logo-animated-glow") checked @endif>
                                    <label for="logo_animated6"> {{getTranslateByKey('main_logo_animation_glow')}} </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{getTranslateByKey('main_animation_speed')}}</label>
                                    <select name="logo_animation_speed" id="logoAnimationSpeed" class="form-control">
                                        @php($logoAnimationSpeed = cacheGlobalSettingsByKey('logo_animation_speed') ?: '6s')
                                        <option value="3s" @if($logoAnimationSpeed === '3s') selected @endif>{{getTranslateByKey('main_speed_fast')}}</option>
                                        <option value="4.5s" @if($logoAnimationSpeed === '4.5s') selected @endif>{{getTranslateByKey('main_speed_faster')}}</option>
                                        <option value="6s" @if($logoAnimationSpeed === '6s') selected @endif>{{getTranslateByKey('main_speed_standard')}}</option>
                                        <option value="8s" @if($logoAnimationSpeed === '8s') selected @endif>{{getTranslateByKey('main_speed_soft')}}</option>
                                        <option value="10s" @if($logoAnimationSpeed === '10s') selected @endif>{{getTranslateByKey('main_speed_slow')}}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>{{getTranslateByKey('main_hover_pause')}}</label>
                                    <div class="form-inline">
                                        <div class="radio radio-inline radio-success">
                                            <input id="logo_hover_pause_yes" type="radio" name="logo_hover_pause" value="yes" @if(cacheGlobalSettingsByKey("logo_hover_pause")==="yes") checked @endif>
                                            <label for="logo_hover_pause_yes">{{getTranslateByKey('common_open')}}</label>
                                        </div>
                                        <div class="radio radio-inline radio-success">
                                            <input id="logo_hover_pause_no" type="radio" name="logo_hover_pause" value="no" @if(cacheGlobalSettingsByKey("logo_hover_pause")!=="yes") checked @endif>
                                            <label for="logo_hover_pause_no">{{getTranslateByKey('common_close')}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-theme-card">
                        <h3 class="mx-theme-card__title">{{getTranslateByKey('main_page_basic_config')}}</h3>
                        <p class="mx-theme-card__desc">{{getTranslateByKey('main_page_basic_config_desc')}}</p>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{getTranslateByKey("global_background_color")}}</label>
                                    <div class="mx-color-field">
                                        <span class="mx-color-swatch" style="background: {{cacheGlobalSettingsByKey("global_bgcolor")}}"></span>
                                        <input type="text" name="global_bgcolor" value="{{cacheGlobalSettingsByKey("global_bgcolor")}}" class="form-control mx-color-text js-color-text">
                                        <input type="color" value="{{cacheGlobalSettingsByKey("global_bgcolor") ?: '#f8fbff'}}" class="mx-color-picker js-color-picker">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>{{getTranslateByKey("global_font")}}</label>
                                    <div class="input-group m-b">
                                        <input type="text" name="global_font" value="{{cacheGlobalSettingsByKey("global_font")}}" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group ">
                            <label>{{getTranslateByKey("page_loading_animation")}}</label>

                            <div class="form-inline">
                                <div class="radio radio-inline radio-success">
                                    <input id="preloader" type="radio" name="preloader" value="off" @if(cacheGlobalSettingsByKey("preloader")=="off") checked @endif >
                                    <label for="preloader"> {{getTranslateByKey("common_close")}} </label>
                                </div>
                                <div class="radio radio-inline radio-success">
                                    <input id="preloader2" type="radio" name="preloader" value="on"  @if(cacheGlobalSettingsByKey("preloader")=="on") checked @endif >
                                    <label for="preloader2"> {{getTranslateByKey("common_open")}} </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group ">
                            <label>{{getTranslateByKey("page_width")}}</label>

                            <div class="form-inline">
                                <div class="radio radio-inline radio-success">
                                    <input id="page_width" type="radio" name="page_width" value="container" @if(cacheGlobalSettingsByKey("page_width")=="container") checked @endif >
                                    <label for="page_width"> {{getTranslateByKey("narrow_screen")}} </label>
                                </div>
                                <div class="radio radio-inline radio-success">
                                    <input id="page_width2" type="radio" name="page_width" value="pull-container"  @if(cacheGlobalSettingsByKey("page_width")=="pull-container") checked @endif >
                                    <label for="page_width2"> {{getTranslateByKey("full_screen")}} </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-theme-card">
                        <h3 class="mx-theme-card__title">{{getTranslateByKey('main_nav_config')}}</h3>
                        <p class="mx-theme-card__desc">{{getTranslateByKey('main_nav_config_desc')}}</p>
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{getTranslateByKey("top_nav_background_color")}}</label>
                                    <div class="mx-color-field">
                                        <span class="mx-color-swatch" style="background: {{cacheGlobalSettingsByKey("nav_bgcolor")}}"></span>
                                        <input type="text" name="nav_bgcolor" value="{{cacheGlobalSettingsByKey("nav_bgcolor")}}" class="form-control mx-color-text js-color-text">
                                        <input type="color" value="{{cacheGlobalSettingsByKey("nav_bgcolor") ?: '#0f172a'}}" class="mx-color-picker js-color-picker">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>{{getTranslateByKey("top_nav_text_color")}}</label>
                                    <div class="mx-color-field">
                                        <span class="mx-color-swatch" style="background: {{cacheGlobalSettingsByKey("nav_color")}}"></span>
                                        <input type="text" name="nav_color" value="{{cacheGlobalSettingsByKey("nav_color")}}" class="form-control mx-color-text js-color-text">
                                        <input type="color" value="{{cacheGlobalSettingsByKey("nav_color") ?: '#ffffff'}}" class="mx-color-picker js-color-picker">
                                    </div>
                                </div>
                            </div>
                            <div class="mx-color-tip">{{getTranslateByKey('main_color_tip')}}</div>
                        </div>

                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>{{getTranslateByKey('main_nav_hover_color')}}</label>
                                    <div class="mx-color-field">
                                        <span class="mx-color-swatch" style="background: {{cacheGlobalSettingsByKey("nav_hover_color") ?: '#60a5fa'}}"></span>
                                        <input type="text" name="nav_hover_color" value="{{cacheGlobalSettingsByKey("nav_hover_color") ?: '#60a5fa'}}" class="form-control mx-color-text js-color-text">
                                        <input type="color" value="{{cacheGlobalSettingsByKey("nav_hover_color") ?: '#60a5fa'}}" class="mx-color-picker js-color-picker">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>{{getTranslateByKey('main_nav_dropdown_bg')}}</label>
                                    <div class="mx-color-field">
                                        <span class="mx-color-swatch" style="background: {{cacheGlobalSettingsByKey("nav_dropdown_bgcolor") ?: '#0f172a'}}"></span>
                                        <input type="text" name="nav_dropdown_bgcolor" value="{{cacheGlobalSettingsByKey("nav_dropdown_bgcolor") ?: '#0f172a'}}" class="form-control mx-color-text js-color-text">
                                        <input type="color" value="{{cacheGlobalSettingsByKey("nav_dropdown_bgcolor") ?: '#0f172a'}}" class="mx-color-picker js-color-picker">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>{{getTranslateByKey('main_nav_dropdown_color')}}</label>
                                    <div class="mx-color-field">
                                        <span class="mx-color-swatch" style="background: {{cacheGlobalSettingsByKey("nav_dropdown_color") ?: '#e2e8f0'}}"></span>
                                        <input type="text" name="nav_dropdown_color" value="{{cacheGlobalSettingsByKey("nav_dropdown_color") ?: '#e2e8f0'}}" class="form-control mx-color-text js-color-text">
                                        <input type="color" value="{{cacheGlobalSettingsByKey("nav_dropdown_color") ?: '#e2e8f0'}}" class="mx-color-picker js-color-picker">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>{{getTranslateByKey('main_nav_radius_style')}}</label>
                                    @php($navRadius = cacheGlobalSettingsByKey('nav_radius') ?: '18px')
                                    <select name="nav_radius" class="form-control">
                                        <option value="0px" @if($navRadius === '0px') selected @endif>{{getTranslateByKey('main_radius_none')}}</option>
                                        <option value="10px" @if($navRadius === '10px') selected @endif>{{getTranslateByKey('main_radius_small')}}</option>
                                        <option value="18px" @if($navRadius === '18px') selected @endif>{{getTranslateByKey('main_radius_standard')}}</option>
                                        <option value="24px" @if($navRadius === '24px') selected @endif>{{getTranslateByKey('main_radius_large')}}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>{{getTranslateByKey('main_nav_shadow')}}</label>
                                    @php($navShadowStyle = cacheGlobalSettingsByKey('nav_shadow_style') ?: 'soft')
                                    <select name="nav_shadow_style" class="form-control">
                                        <option value="none" @if($navShadowStyle === 'none') selected @endif>{{getTranslateByKey('main_shadow_none')}}</option>
                                        <option value="soft" @if($navShadowStyle === 'soft') selected @endif>{{getTranslateByKey('main_shadow_soft')}}</option>
                                        <option value="strong" @if($navShadowStyle === 'strong') selected @endif>{{getTranslateByKey('main_shadow_strong')}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group ">
                            <label>{{getTranslateByKey("top_nav_effect")}}</label>

                            <div class="form-inline">
                                <div class="radio radio-inline radio-success">
                                    <input id="nav_position" type="radio" name="nav_position" value="static" @if(cacheGlobalSettingsByKey("nav_position")=="static") checked @endif >
                                    <label for="nav_position"> {{getTranslateByKey("follow_page_scroll")}} </label>
                                </div>
                                <div class="radio radio-inline radio-success">
                                    <input id="nav_position2" type="radio" name="nav_position" value="sticky"  @if(cacheGlobalSettingsByKey("nav_position")=="sticky") checked @endif >
                                    <label for="nav_position2"> {{getTranslateByKey("sticky_top")}} </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-theme-card">
                        <h3 class="mx-theme-card__title">{{getTranslateByKey('main_extend_code')}}</h3>
                        <p class="mx-theme-card__desc">{{getTranslateByKey('main_extend_code_desc')}}</p>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-6 mt-20">
                                    <label>
                                        {{getTranslateByKey("head_code")}}（{{getTranslateByKey("head_code_notes")}}）
                                    </label>
                                    <textarea style="height: 150px;" placeholder="{{getTranslateByKey("head_code")}}" name="head_codes" class="form-control"
                                              rows="4">{{cacheGlobalSettingsByKey('head_codes')}}</textarea>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <label>
                                        {{getTranslateByKey("foot_code")}}（{{getTranslateByKey("foot_code_notes")}}）
                                    </label>
                                    <textarea style="height: 150px;" placeholder="{{getTranslateByKey("foot_code")}}" name="foot_codes" class="form-control"
                                              rows="4">{{cacheGlobalSettingsByKey('foot_codes')}}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-theme-savebar">
                        <button type="button" id="diy_button" class="btn btn-primary margin-l-5 mx-sm-3">{{getTranslateByKey("common_save")}}</button>
                    </div>

                </form>

            </div>
            </div>
        </div>
    </div>
    @include('admin.public.footer')
</section>
@include('admin.public.js',['load'=> ["custom"]])
<script>

    $(function () {
        function normalizeHexColor(value) {
            const color = (value || '').trim();
            if (/^#([0-9a-fA-F]{3}){1,2}$/.test(color)) {
                if (color.length === 4) {
                    return '#' + color[1] + color[1] + color[2] + color[2] + color[3] + color[3];
                }
                return color;
            }
            return null;
        }

        function syncColorField($field, color) {
            const normalized = normalizeHexColor(color);
            if (!normalized) {
                return;
            }
            $field.find('.js-color-text').val(normalized);
            $field.find('.js-color-picker').val(normalized);
            $field.find('.mx-color-swatch').css('background', normalized);
        }

        $('.mx-color-field').each(function () {
            syncColorField($(this), $(this).find('.js-color-text').val());
        });

        $('.js-color-picker').on('input change', function () {
            const $field = $(this).closest('.mx-color-field');
            syncColorField($field, $(this).val());
        });

        $('.js-color-text').on('input blur', function () {
            const $field = $(this).closest('.mx-color-field');
            syncColorField($field, $(this).val());
        });

        function updateLogoPreview() {
            const animationClass = $('input[name=logo_animated]:checked').val() || '';
            const speed = $('#logoAnimationSpeed').val() || '6s';
            $('#logoPreviewTarget')
                .attr('class', 'mx-logo-preview__item ' + animationClass)
                .css('--mx-logo-animation-duration', speed);
        }

        $('input[name=logo_animated], #logoAnimationSpeed').on('change', function () {
            updateLogoPreview();
        });
        updateLogoPreview();

        $("#diy_button").click(function () {

            popup({type:'load',msg:"{{getTranslateByKey("requesting")}}",delay:800,callBack:function(){
                    $.ajax({
                        "method":"post",
                        "url":"{{url('admin/theme/diy?m=').$_GET['m']}}",
                        "data":  new FormData($('#diy_form')[0]),                                  //$("#userinfo_form").serialize(),
                        "dataType":'json',
                        "cache":false,
                        "processData": false,
                        "contentType": false,
                        "success":function (res) {
                            if(res.status==200){
                                popup({type:"success",msg:res.msg,delay:2000});
                                setTimeout(function () {
                                    location.reload()
                                },2000)
                            }else{
                                popup({type:"error",msg:res.msg,delay:2000});
                            }
                        },
                        "error":function (res) {
                            console.log(res);
                        }
                    })
                }});
        })

    })

</script>
</body>

</html>
