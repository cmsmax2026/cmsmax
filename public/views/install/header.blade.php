@php
    $installCurrentLang = session('install_current_language') ?: 'zh-CN';
    $installStep = min(max(intval(request()->query('install', 1)), 1), 3);
    $installLangOptions = [
        'zh-CN' => '简体中文',
        'zh-TW' => '繁體中文',
        'en' => 'English',
    ];
@endphp
<div class="container">
    <div class="install-hero">
        <div>
            <h1 class="install-hero__title">{{ installTrans('install_wizard_title', ['name' => $cms_name]) }}</h1>
            <p class="install-hero__desc">{{ installTrans('install_wizard_desc') }}</p>
        </div>
        <div class="text-right">
            <span class="install-version-chip">v{{config('app.app_version')}}</span>
            <div class="mt-2">
                <span class="install-badge"><i class="fa fa-shield"></i> {{ installTrans('install_badge_pipeline') }}</span>
                <span class="install-badge"><i class="fa fa-database"></i> {{ installTrans('install_badge_data_init') }}</span>
            </div>
            <div class="mt-2">
                @foreach($installLangOptions as $installLangCode => $installLangLabel)
                    <a href="{{ url('/install', ['install' => $installStep, 'lang' => $installLangCode]) }}"
                       class="btn btn-sm {{ $installCurrentLang === $installLangCode ? 'btn-primary' : 'btn-outline-secondary' }}"
                       style="margin-left: 4px;">{{ $installLangLabel }}</a>
                @endforeach
            </div>
        </div>
    </div>
</div>
