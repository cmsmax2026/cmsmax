<!DOCTYPE html>
<html lang="{{ currentHomeLang() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="token" id="token" content="{{csrf_token()}}">
    {!! hook("Seo",compact("model","data"))[0] !!}
    <!-- Favicon  -->
    <link rel="icon" type="image/png" href="{{GetLocalFileByPath(cacheGlobalSettingsByKey('webicon'))}}">
    <!-- Include StyleSheets -->
    <link rel="stylesheet" href="@themeAsset('css/style.min.css')">
    <link rel="stylesheet" href="@themeAsset('css/h-style.css')">
    <link rel="stylesheet" href="@themeAsset('css/ui-upgrade.css')">
    <!-- <link rel="stylesheet" href="css/custom.css"> -->
    <script src="@themeAsset('js/modernizr.js')"></script>
    <script>
        var urlPre = "{{url('/')}}/";
    </script>
    {{-- 主题配置自动注入 CSS 变量：--mx-primary-color 等，插件可通过 ThemeCssVariables 钩子追加 --}}
    {!! theme_css_variables() !!}
    {{-- 插件挂载点：注入额外的 CSS / meta --}}
    {!! theme_hook('ThemeHead') !!}
    {!! cacheGlobalSettingsByKey('head_codes') !!}
</head>
