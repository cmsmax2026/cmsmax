@php($currentTheme = trim((string) ($_GET['m'] ?? 'default')) ?: 'default')
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
            <li>
                <a href="{{url("admin/theme/themeMenuList?m=".$_GET['m'])}}"><i class="fa fa-sitemap"></i>{{getTranslateByKey('menu_list')}}</a>
            </li>
            <li>
                <a href="{{url("admin/theme/themeMenuAdd?m=".$_GET['m'])}}"><i class="fa fa-plus-circle"></i>{{getTranslateByKey('main_add_menu')}}</a>
            </li>
            <li>
                <a href="javascript:;"><i class="fa fa-paint-brush"></i>{{getTranslateByKey('main_theme_label')}}{{$currentTheme}}</a>
            </li>
        </ul>
    </nav>
</div>
