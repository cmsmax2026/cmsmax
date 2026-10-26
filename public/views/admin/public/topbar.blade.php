<style>
    .admin-topbar-actions {
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .admin-topbar-actions > .nav-item {
        display: flex;
        align-items: center;
        margin-right: 0 !important;
    }
    .admin-topbar-shortcuts-item {
        margin: 0 4px;
    }
    .admin-topbar-admin-cluster {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-right: 6px;
    }
    .admin-topbar-shortcuts {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 7px 9px;
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid #e3e8ef;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }
    .admin-topbar-shortcut {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid #d8e0ea;
        background: #f8fafc;
        color: #334155 !important;
        font-size: 13px;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
        text-decoration: none !important;
        transition: all .2s ease;
    }
    .admin-topbar-shortcut:hover,
    .admin-topbar-shortcut:focus {
        color: #0f172a !important;
        border-color: #c5d2e3;
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(59, 130, 246, 0.10);
    }
    .admin-topbar-shortcut-icon {
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .admin-topbar-shortcut-icon svg {
        width: 14px;
        height: 14px;
        display: block;
    }
    .admin-topbar-shortcut-cache .admin-topbar-shortcut-icon {
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
    }
    .admin-topbar-shortcut-cleanup .admin-topbar-shortcut-icon {
        background: rgba(217, 119, 6, 0.12);
        color: #d97706;
    }
    .admin-topbar-shortcut-site .admin-topbar-shortcut-icon {
        background: rgba(22, 163, 74, 0.12);
        color: #16a34a;
    }
    .admin-topbar-update .nav-link {
        display: inline-flex;
        align-items: center;
        min-height: 38px;
        padding: 8px 14px !important;
        border-radius: 999px;
        border: 1px solid rgba(220, 38, 38, 0.18);
        background: rgba(254, 242, 242, 0.92);
        color: #dc2626 !important;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
    }
    .admin-topbar-update {
        display: inline-flex;
        align-items: center;
        flex-shrink: 0;
    }
    .admin-topbar-update .nav-link:hover,
    .admin-topbar-update .nav-link:focus {
        background: #ffffff;
        border-color: rgba(220, 38, 38, 0.28);
        box-shadow: 0 8px 18px rgba(220, 38, 38, 0.10);
    }
    .admin-topbar-user > .nav-link,
    .admin-topbar-language > .nav-link {
        white-space: nowrap;
    }
    @media (max-width: 1400px) {
        .admin-topbar-shortcuts {
            gap: 8px;
            padding: 6px 8px;
        }
        .admin-topbar-shortcut {
            padding: 8px 12px;
        }
    }
    @media (max-width: 1199px) {
        .admin-topbar-admin-cluster {
            width: 100%;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        .admin-topbar-shortcuts-item {
            width: auto;
        }
        .admin-topbar-shortcuts {
            flex-wrap: wrap;
            justify-content: flex-end;
        }
    }
</style>
<div class="top-bar light-top-bar">
    <div class="container-fluid">
        <div class="">
            <nav class="navbar navbar-expand-lg navbar-light">
                {{--                <a class="navbar-brand" href="#">Navbar</a>--}}
                <a class="admin-logo" href="{{url('admin/index')}}" style="width: auto!important;">
                    <h1 style="color: #000;">
                        {{getTranslateByKey("admin_panel")}}
                    </h1>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">


                    <ul class="navbar-nav mr-auto">
                    </ul>
                    <ul class="navbar-nav navbar-padding ml-auto bg-light admin-topbar-actions">
                        <li class="nav-item admin-topbar-shortcuts-item">
                            <div class="admin-topbar-admin-cluster">
                                <div class="updateCMS admin-topbar-update">
                                    {!! hook("CmsUpdateVersion",['version'=>env("APP_VERSION"),"moduleName"=>"System","cssClass"=>"nav-link"])[0] !!}
                                </div>
                                <div class="admin-topbar-shortcuts" role="group" aria-label="{{getTranslateByKey('main_topbar_shortcuts')}}">
                                    <a class="admin-topbar-shortcut admin-topbar-shortcut-cache" href="javascript:;"
                                       onclick="clearCache(); return false;">
                                        <span class="admin-topbar-shortcut-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path d="M7 7h10v3H7zM6 12h12v5a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1z" fill="currentColor"></path>
                                                <path d="M8 4h8v3H8zM9 14h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                                            </svg>
                                        </span>
                                        <span>{{getTranslateByKey("clear_cache")}}</span>
                                    </a>
                                    <a class="admin-topbar-shortcut admin-topbar-shortcut-cleanup" href="javascript:;"
                                       onclick="advancedCleanup(); return false;">
                                        <span class="admin-topbar-shortcut-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path d="M9 4h6M4 7h16M7 7l1 11a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2l1-11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M10 11v4M14 11v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                                            </svg>
                                        </span>
                                        <span>{{getTranslateByKey("advanced_cleanup")}}</span>
                                    </a>
                                    <a class="admin-topbar-shortcut admin-topbar-shortcut-site" href="{{url("/")}}"
                                       target="_blank" rel="noopener noreferrer">
                                        <span class="admin-topbar-shortcut-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path d="M12 3a9 9 0 1 0 0 18a9 9 0 0 0 0-18Z" stroke="currentColor" stroke-width="1.8"></path>
                                                <path d="M3.5 12h17M12 3c2.2 2.4 3.4 5.4 3.4 9S14.2 18.6 12 21M12 3c-2.2 2.4-3.4 5.4-3.4 9S9.8 18.6 12 21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></path>
                                            </svg>
                                        </span>
                                        <span>{{getTranslateByKey("shopping_site")}}</span>
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item dropdown mr-2 admin-topbar-language">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{session("admin_current_language")["name"]}}
                            </a>
                            <ul class="dropdown-menu top-dropdown" id="dropdown-menu">
                                @foreach(\Modules\Main\Services\ServiceModel::getLangList() as $kl=>$vl)
                                    <li>
                                        <a class="dropdown-item" href="{{url('admin/changeLang?lang='.$kl)}}">
                                            {{$vl}}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="nav-item dropdown admin-topbar-user">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{session("admin_info")['username']}}({{getTranslateByKey("system_administrator")}})
                            </a>
                            <ul class="dropdown-menu top-dropdown" id="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{url('admin/myinfo')}}"><i class="icon-user"></i>
                                        {{getTranslateByKey("profile")}}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{url('admin/logout')}}"><i class="icon-logout"></i>
                                        {{getTranslateByKey("logout")}}</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</div>
