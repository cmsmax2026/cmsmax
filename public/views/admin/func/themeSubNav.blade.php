{{-- 主题管理子导航：主题配置 / 更新槽位 / 短标签 / 钩子说明 / 页面配置 / 菜单管理 --}}
@php
    $theme = $theme ?? \App\Support\Theme\ThemeManager::current();
    $active = $active ?? '';
    $items = [
        ['key' => 'preview', 'label' => getTranslateByKey('main_front_preview'), 'url' => url('admin/theme/setting?m=' . $theme)],
        ['key' => 'settings', 'label' => getTranslateByKey('theme_configuration'), 'url' => url('admin/theme/themeSettings?m=' . $theme)],
        ['key' => 'slots', 'label' => getTranslateByKey('main_update_slots'), 'url' => url('admin/theme/themeSlots?m=' . $theme)],
        ['key' => 'shorttags', 'label' => getTranslateByKey('main_shorttags'), 'url' => url('admin/theme/themeShorttags?m=' . $theme)],
        ['key' => 'template', 'label' => getTranslateByKey('main_template_editor'), 'url' => url('admin/theme/templateEditor?m=' . $theme)],
        ['key' => 'hooks', 'label' => getTranslateByKey('main_hooks_desc'), 'url' => url('admin/theme/themeHooks?m=' . $theme)],
        ['key' => 'diy', 'label' => getTranslateByKey('page_configuration'), 'url' => url('admin/theme/diy?m=' . $theme)],
        ['key' => 'menu', 'label' => getTranslateByKey('menu_management'), 'url' => url('admin/theme/themeMenuList?m=' . $theme)],
    ];
@endphp
<div class="main-horizontal-nav">
    <nav>
        <div class="menu-toggle">
            <h3>{{ getTranslateByKey("menu_navigation") }}</h3>
            <button type="button" id="menu-btn">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <ul id="respMenu" class="ace-responsive-menu" data-menu-style="horizontal">
            <li>
                <a href="{{ url("admin/theme") }}"><i class="fa fa-arrow-circle-left"></i> {{ getTranslateByKey('return_theme_list') }}</a>
            </li>
            @foreach($items as $item)
                <li class="{{ $active === $item['key'] ? 'active' : '' }}">
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                </li>
            @endforeach
            <li>
                <a href="{{ url("admin/theme/preview?m=" . $theme) }}" target="_blank">
                    <i class="fa fa-eye"></i> {{ getTranslateByKey('main_preview_front') }}
                </a>
            </li>
        </ul>
    </nav>
</div>

<div class="row page-header" style="margin-bottom: 0px;">
    <div class="col-lg-8 align-self-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url("admin/index") }}">{{ getTranslateByKey("common_home_page") }}</a></li>
            <li class="breadcrumb-item"><a href="{{ url("admin/theme") }}">{{ getTranslateByKey('main_theme_manage') }}</a></li>
            <li class="breadcrumb-item active">{{ $pageData['title'] ?? getTranslateByKey('main_theme') }}</li>
        </ol>
    </div>
    <div class="col-lg-4 align-self-center text-right">
        <span class="text-muted">{{ getTranslateByKey('main_current_theme') }}</span>
        <strong>{{ $theme }}</strong>
    </div>
</div>
