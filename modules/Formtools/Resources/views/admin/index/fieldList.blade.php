@include(moduleAdminTemplate($moduleName)."public.header")
<style>
    .h-invitation-list {
        cursor: pointer;
    }

    .ft-field-index {
        display: grid;
        gap: 18px;
    }

    .ft-field-note {
        margin-bottom: 0;
        border-radius: 14px;
        border: 1px solid #dbeafe;
        background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
        color: #1e3a8a;
        box-shadow: 0 10px 26px rgba(30, 64, 175, 0.08);
    }

    .ft-field-overview {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .ft-field-stat {
        padding: 18px 20px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    }

    .ft-field-stat__label {
        margin: 0 0 8px;
        font-size: 12px;
        color: #64748b;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .ft-field-stat__value {
        margin: 0;
        font-size: 28px;
        line-height: 1;
        font-weight: 700;
        color: #0f172a;
    }

    .ft-field-stat__desc {
        margin-top: 8px;
        font-size: 13px;
        color: #94a3b8;
    }

    .ft-field-panel {
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
    }

    .ft-field-toolbar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px;
        border-bottom: 1px solid #edf2f7;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .ft-field-toolbar__title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .ft-field-toolbar__desc {
        margin: 6px 0 0;
        color: #64748b;
        line-height: 1.8;
    }

    .ft-field-toolbar__sub {
        margin-top: 8px;
        color: #94a3b8;
        line-height: 1.8;
    }

    .ft-field-toolbar__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .ft-field-toolbar__actions .btn,
    .ft-field-toolbar__actions .label {
        border-radius: 999px;
        font-weight: 600;
        min-width: 110px;
        text-align: center;
        box-shadow: none;
    }

    .ft-field-table-wrap {
        padding: 0 22px 18px;
    }

    .ft-field-table {
        margin-bottom: 0;
    }

    .ft-field-table > thead > tr > th {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        background: #f8fafc;
    }

    .ft-field-table > tbody > tr > td {
        padding: 18px 16px;
        border-top: 1px solid #edf2f7;
        vertical-align: top;
    }

    .ft-field-table > tbody > tr:hover {
        background: #f8fbff;
    }

    .ft-field-main__title {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
    }

    .ft-field-meta,
    .ft-field-logic__item,
    .ft-field-db__item {
        margin-top: 8px;
        color: #64748b;
        line-height: 1.8;
    }

    .ft-field-code {
        display: inline-block;
        margin-left: 6px;
        padding: 2px 8px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-family: Consolas, Monaco, monospace;
        font-size: 12px;
    }

    .ft-field-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .ft-field-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #f1f5f9;
        color: #334155;
    }

    .ft-field-badge--success {
        background: #dcfce7;
        color: #15803d;
    }

    .ft-field-badge--danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    .ft-field-badge--primary {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .ft-field-switches {
        display: grid;
        gap: 8px;
    }

    .ft-field-switch {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
    }

    .ft-field-switch__name {
        color: #475569;
        line-height: 1.6;
    }

    .ft-field-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        min-width: 220px;
    }

    .ft-field-actions .btn {
        border-radius: 999px;
        font-weight: 600;
    }

    .ft-field-empty {
        padding: 34px 20px !important;
        text-align: center;
        color: #94a3b8;
    }

    @media (max-width: 1199px) {
        .ft-field-overview {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991px) {
        .ft-field-toolbar {
            flex-direction: column;
        }

        .ft-field-toolbar__actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 767px) {
        .ft-field-overview {
            grid-template-columns: 1fr;
        }

        .ft-field-table-wrap {
            padding: 0 14px 14px;
        }

        .ft-field-toolbar {
            padding: 18px 14px;
        }

        .ft-field-actions {
            min-width: 0;
        }
    }
</style>
<body>

<!--                        Topbar End                              -->
<!-- ============================================================== -->


<!-- ============================================================== -->
<!-- 						Navigation Start 						-->
<!-- ============================================================== -->

@include(moduleAdminTemplate($moduleName)."public.nav")
<!-- ============================================================== -->
<!-- 						Navigation End	 						-->
<!-- ============================================================== -->

<!-- Page container -->
<div class="page-container">

    <!-- Page content -->
    <div class="page-content">

    @include(moduleAdminTemplate($moduleName)."public.left")


    <!-- Main content -->
        <div class="content-wrapper">


            <!-- Content area -->
            <div class="content" style="margin-top: 1rem;">
            @include(moduleAdminTemplate($moduleName)."public.crumb",['breadcrumb'=>[$pageData['title'],$pageData['subtitle']]])
            @php
                $configuredFieldCount = count($configuredFields ?? []);
                $tableFieldCount = count($tableColumns ?? []);
                $frontendFormCount = collect($configuredFields ?? [])->where('is_show_home_form', '1')->count();
                $frontendSearchCount = collect($configuredFields ?? [])->where('is_show_home_list_search', '1')->count();
                $downloadFieldCount = collect($configuredFields ?? [])->filter(function ($field) {
                    return in_array((string) ($field['formtype'] ?? ''), ['upload', 'uploadAjax', 'file'], true);
                })->count();
            @endphp

            <!-- Bordered striped table -->
                <div class="ft-field-index">
                    <div class="alert alert-info alert-styled-left ft-field-note">
                        <span>{{ getTranslateByKey('ft_field_note_pre') }}<strong>{{ getTranslateByKey('ft_field_note_strong') }}</strong>{{ getTranslateByKey('ft_field_note_post') }}</span>
                    </div>
                    <div class="ft-field-overview">
                        <div class="ft-field-stat">
                            <p class="ft-field-stat__label">{{ getTranslateByKey('ft_field_stat_configured') }}</p>
                            <p class="ft-field-stat__value">{{$configuredFieldCount}}</p>
                            <div class="ft-field-stat__desc">{{ getTranslateByKey('ft_field_stat_configured_desc') }}</div>
                        </div>
                        <div class="ft-field-stat">
                            <p class="ft-field-stat__label">{{ getTranslateByKey('ft_field_stat_table_count') }}</p>
                            <p class="ft-field-stat__value">{{$tableFieldCount}}</p>
                            <div class="ft-field-stat__desc">{{ getTranslateByKey('ft_field_stat_table_count_desc', 'admin', ['name' => $tableName]) }}</div>
                        </div>
                        <div class="ft-field-stat">
                            <p class="ft-field-stat__label">{{ getTranslateByKey('ft_field_stat_form') }}</p>
                            <p class="ft-field-stat__value">{{$frontendFormCount}}</p>
                            <div class="ft-field-stat__desc">{{ getTranslateByKey('ft_field_stat_form_desc') }}</div>
                        </div>
                        <div class="ft-field-stat">
                            <p class="ft-field-stat__label">{{ getTranslateByKey('ft_field_stat_search') }}</p>
                            <p class="ft-field-stat__value">{{$frontendSearchCount}}</p>
                            <div class="ft-field-stat__desc">{{ getTranslateByKey('ft_field_stat_search_desc') }}</div>
                        </div>
                        <div class="ft-field-stat">
                            <p class="ft-field-stat__label">{{ getTranslateByKey('ft_field_stat_download') }}</p>
                            <p class="ft-field-stat__value">{{$downloadFieldCount}}</p>
                            <div class="ft-field-stat__desc">{{ getTranslateByKey('ft_field_stat_download_desc') }}</div>
                        </div>
                    </div>
                    <div class="ft-field-panel">
                        <div class="ft-field-toolbar">
                            <div>
                                <h3 class="ft-field-toolbar__title">{{ getTranslateByKey('ft_field_manage_with', 'admin', ['name' => $pageData['data']->name]) }}</h3>
                                <p class="ft-field-toolbar__desc">{{ getTranslateByKey('ft_field_toolbar_desc') }}</p>
                                <div class="ft-field-toolbar__sub">
                                    {{ getTranslateByKey('ft_field_current_table') }}<span class="ft-field-code">{{ $tableName }}</span>
                                    {{ getTranslateByKey('ft_field_current_model') }}<span class="ft-field-code">{{ $pageData['data']->identification }}</span>
                                </div>
                            </div>
                            <div class="ft-field-toolbar__actions">
                                <a class="btn btn-info btn-sm" href="{{url("admin/formtools/fieldAdd?id=".$pageData['id'])}}">{{ getTranslateByKey('ft_field_add') }}</a>
                                <a class="btn btn-success btn-sm" href="{{url("admin/formtools/modelEdit?id=".$pageData['id'])}}">{{ getTranslateByKey('ft_model_config') }}</a>
                                <a class="btn btn-primary btn-sm" href="{{url("admin/formtools/model?moduleName={$moduleName}&action=List&model=".$pageData['data']->identification)}}">{{ getTranslateByKey('ft_content_manage') }}</a>
                                @if($pageData['data']->access_identification)
                                    <a class="btn btn-default btn-sm" target="_blank" href="{{ model_list_url($pageData['data']->access_identification) }}">{{ getTranslateByKey('ft_home_preview') }}</a>
                                @endif
                            </div>
                        </div>
                        <div class="table-responsive ft-field-table-wrap">
                        <table class="table ft-field-table m-b-none">
                            <thead>
                            <tr>
                                <th>{{ getTranslateByKey('ft_field_th_info') }}</th>
                                <th>{{ getTranslateByKey('ft_field_th_db') }}</th>
                                <th>{{ getTranslateByKey('ft_field_th_display') }}</th>
                                <th>{{ getTranslateByKey('ft_field_th_logic') }}</th>
                                <th>{{ getTranslateByKey('operation') }}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @forelse($colunmListDetaill as $key=>$d)
                                <tr>
                                    <td>
                                        <div class="ft-field-main">
                                            <h4 class="ft-field-main__title">
                                                {{$d['remark'] ?: $d['name']}}
                                                <span class="ft-field-code">#{{$key + 1}}</span>
                                            </h4>
                                            <div class="ft-field-meta">
                                                {{ getTranslateByKey('ft_field_name') }} <span class="ft-field-code">{{ $d['name'] ?: getTranslateByKey('ft_not_configured') }}</span>
                                            </div>
                                            <div class="ft-field-meta">
                                                {{ getTranslateByKey('ft_field_identification') }} <span class="ft-field-code">{{$d['identification']}}</span>
                                            </div>
                                            <div class="ft-field-badges">
                                                @if(($d['required'] ?? '') === 'required')
                                                    <span class="ft-field-badge ft-field-badge--danger">{{ getTranslateByKey('ft_api_required') }}</span>
                                                @else
                                                    <span class="ft-field-badge">{{ getTranslateByKey('ft_field_not_required') }}</span>
                                                @endif
                                                <span class="ft-field-badge ft-field-badge--primary">{{ getTranslateByKey('ft_field_formtype_with', 'admin', ['name' => $d['formtype'] ?: 'text']) }}</span>
                                                @if(!empty($d['is_download_field']))
                                                    <span class="ft-field-badge ft-field-badge--success">{{ getTranslateByKey('ft_field_download_badge') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ft-field-db__item">
                                            {{ getTranslateByKey('ft_field_db_column') }} <span class="ft-field-code">{{$tableName}}.{{$d['identification']}}</span>
                                        </div>
                                        <div class="ft-field-db__item">
                                            {{ getTranslateByKey('ft_field_type_with', 'admin', ['name' => $d['fieldtype'] ?: 'string']) }}
                                        </div>
                                        <div class="ft-field-db__item">
                                            {{ getTranslateByKey('ft_field_maxlength_with', 'admin', ['name' => (string) ($d['maxlength'] ?? '0') !== '' ? $d['maxlength'] : '0']) }}
                                        </div>
                                        <div class="ft-field-badges">
                                            @if(!empty($d['column_exists']))
                                                <span class="ft-field-badge ft-field-badge--success">{{ getTranslateByKey('ft_field_in_db') }}</span>
                                            @else
                                                <span class="ft-field-badge ft-field-badge--danger">{{ getTranslateByKey('ft_field_not_in_db') }}</span>
                                            @endif
                                            <span class="ft-field-badge">{{ getTranslateByKey('ft_field_index_with', 'admin', ['name' => $d['isindex'] ?: 'NOINDEX']) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ft-field-switches">
                                            <div class="ft-field-switch">
                                                <span class="ft-field-switch__name">{{ getTranslateByKey('ft_field_admin_list') }}</span>
                                                @if(($d['is_show_list'] ?? '2') == 1)
                                                    <span class="ft-field-badge ft-field-badge--success">{{ getTranslateByKey('common_open') }}</span>
                                                @else
                                                    <span class="ft-field-badge ft-field-badge--danger">{{ getTranslateByKey('common_close') }}</span>
                                                @endif
                                            </div>
                                            <div class="ft-field-switch">
                                                <span class="ft-field-switch__name">{{ getTranslateByKey('ft_field_admin_search') }}</span>
                                                @if(($d['is_show_admin_list_search'] ?? '2') == 1)
                                                    <span class="ft-field-badge ft-field-badge--success">{{ getTranslateByKey('common_open') }}</span>
                                                @else
                                                    <span class="ft-field-badge ft-field-badge--danger">{{ getTranslateByKey('common_close') }}</span>
                                                @endif
                                            </div>
                                            <div class="ft-field-switch">
                                                <span class="ft-field-switch__name">{{ getTranslateByKey('ft_field_home_form') }}</span>
                                                @if(($d['is_show_home_form'] ?? '2') == 1)
                                                    <span class="ft-field-badge ft-field-badge--success">{{ getTranslateByKey('common_open') }}</span>
                                                @else
                                                    <span class="ft-field-badge ft-field-badge--danger">{{ getTranslateByKey('common_close') }}</span>
                                                @endif
                                            </div>
                                            <div class="ft-field-switch">
                                                <span class="ft-field-switch__name">{{ getTranslateByKey('ft_field_home_list') }}</span>
                                                @if(($d['is_show_home_list'] ?? '1') == 1)
                                                    <span class="ft-field-badge ft-field-badge--success">{{ getTranslateByKey('common_open') }}</span>
                                                @else
                                                    <span class="ft-field-badge ft-field-badge--danger">{{ getTranslateByKey('common_close') }}</span>
                                                @endif
                                            </div>
                                            <div class="ft-field-switch">
                                                <span class="ft-field-switch__name">{{ getTranslateByKey('ft_field_home_detail') }}</span>
                                                @if(($d['is_show_home_detail'] ?? '1') == 1)
                                                    <span class="ft-field-badge ft-field-badge--success">{{ getTranslateByKey('common_open') }}</span>
                                                @else
                                                    <span class="ft-field-badge ft-field-badge--danger">{{ getTranslateByKey('common_close') }}</span>
                                                @endif
                                            </div>
                                            @if(!empty($d['is_download_field']))
                                                <div class="ft-field-switch">
                                                    <span class="ft-field-switch__name">{{ getTranslateByKey('ft_field_download_entry') }}</span>
                                                    @if(!empty($d['download_ready']))
                                                        <span class="ft-field-badge ft-field-badge--success">{{ getTranslateByKey('ft_field_download_ready') }}</span>
                                                    @else
                                                        <span class="ft-field-badge ft-field-badge--danger">{{ getTranslateByKey('ft_field_download_need_detail') }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                            <div class="ft-field-switch">
                                                <span class="ft-field-switch__name">{{ getTranslateByKey('ft_field_home_search') }}</span>
                                                @if(($d['is_show_home_list_search'] ?? '2') == 1)
                                                    <span class="ft-field-badge ft-field-badge--success">{{ getTranslateByKey('common_open') }}</span>
                                                @else
                                                    <span class="ft-field-badge ft-field-badge--danger">{{ getTranslateByKey('common_close') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ft-field-logic__item">{{ getTranslateByKey('ft_field_rule') }}<span class="ft-field-code">{{ $d['rule'] ?: 'unlimited' }}</span></div>
                                        @if(!empty($d['is_download_field']))
                                            <div class="ft-field-logic__item">{{ getTranslateByKey('ft_field_download_desc') }}</div>
                                        @endif
                                        <div class="ft-field-logic__item">{{ getTranslateByKey('ft_field_foreign') }}<span class="ft-field-code">{{ $d['foreign'] ?: getTranslateByKey('ft_field_none') }}</span></div>
                                        <div class="ft-field-logic__item">{{ getTranslateByKey('ft_field_foreign_key') }}<span class="ft-field-code">{{ $d['foreign_key'] ?: getTranslateByKey('ft_field_none') }}</span></div>
                                    </td>
                                    <td>
                                        <div class="ft-field-actions">
                                            @if($key>0)
                                                <a href="{{url("admin/formtools/fieldMove?move_type=1&id=".$pageData['id']."&identification=".$d['identification'])}}"
                                                   class="btn btn-primary btn-xs">
                                                    {{ getTranslateByKey('ft_field_move_up') }}
                                                </a>
                                            @endif
                                            @if($key!=(count($colunmListDetaill)-1))
                                                <a href="{{url("admin/formtools/fieldMove?move_type=2&id=".$pageData['id']."&identification=".$d['identification'])}}"
                                                   class="btn btn-info btn-xs">
                                                    {{ getTranslateByKey('ft_field_move_down') }}
                                                </a>
                                            @endif
                                            <a href="{{url("admin/formtools/fieldEdit?&id=".$pageData['id']."&identification=".$d['identification'])}}"
                                               class="btn btn-success btn-xs">
                                                {{ getTranslateByKey('common_edit') }}
                                            </a>
                                            <a onclick="delField('{{url("admin/formtools/fieldDel?id=".$pageData['id']."&identification=".$d['identification'])}}','{{ addslashes($d['remark'] ?: $d['name'] ?: $d['identification']) }}','{{ $d['identification'] }}')"
                                               class="btn btn-danger btn-xs ">
                                                {{ getTranslateByKey('common_delete') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="ft-field-empty">
                                        {{ getTranslateByKey('no_data') }}
                                    </td>
                                </tr>
                            @endforelse


                            </tbody>
                        </table>
                        </div>
                        {{--<div class="col-sm-12 text-right text-center-xs">
                            @if(count($data)>0)
                                {{ $pageData['data']->links() }}
                            @endif
                        </div>--}}
                    </div>
                </div>
                <!-- /bordered striped table -->


                @include(moduleAdminTemplate($moduleName)."public.footer")


            </div>
            <!-- /content area -->


        </div>
        <!-- /page content -->

    </div>
    <!-- /page container -->

    <!-- 						Content End		 						-->
    <!-- ============================================================== -->
    @include(moduleAdminTemplate($moduleName)."public.js")
    {{--<script type="text/javascript" src="{{asset("assets/module")}}/js/pages/dashboard.js"></script>--}}
    <script>
        function delField(url, title, identification) {
            layer.confirm('{{ getTranslateByKey('ft_field_del_confirm', 'admin', ['name' => ':TITLE:', 'identification' => ':IDENT:']) }}'.replace(':TITLE:', title).replace(':IDENT:', identification), {
                title: '{{ getTranslateByKey('ft_operation_tip') }}',
                btn: ['{{ getTranslateByKey('common_ensure') }}', '{{ getTranslateByKey('common_cancel') }}']
            }, function (index, layero) {
                window.location.href = url;
            }, function (index) {
            });
        }
    </script>
</body>
</html>

