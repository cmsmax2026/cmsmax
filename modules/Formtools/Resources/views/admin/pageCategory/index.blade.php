@php($moduleName = $pageData['moduleName'] ?? 'Formtools')
@php($moduleViewName = strtolower($moduleName))
@include(moduleAdminTemplate($moduleName)."public.header")
<style>
    .page-content > .content-wrapper {
        min-width: 0;
    }

    .ft-page-category {
        display: grid;
        gap: 18px;
        min-width: 0;
    }

    .ft-page-category__note {
        margin-bottom: 0;
        border-radius: 16px;
        border: 1px solid #dbeafe;
        background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
        color: #1e3a8a;
    }

    .ft-page-category__panel {
        overflow: hidden;
        border-radius: 20px;
        background: #fff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.08);
        min-width: 0;
        max-width: 100%;
    }

    .ft-page-category__toolbar {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 24px;
        border-bottom: 1px solid #eef2f7;
    }

    .ft-page-category__title {
        margin: 0 0 8px;
        color: #0f172a;
        font-size: 20px;
        font-weight: 700;
    }

    .ft-page-category__desc {
        margin: 0;
        color: #64748b;
        line-height: 1.8;
    }

    .ft-page-category__table-wrap {
        padding: 0 22px 18px;
        min-width: 0;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: visible;
    }

    .ft-page-category__table {
        margin-bottom: 0;
        min-width: 920px;
    }

    .ft-page-category__table > thead > tr > th {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        background: #f8fafc;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .ft-page-category__table > tbody > tr > td {
        padding: 16px;
        border-top: 1px solid #edf2f7;
        vertical-align: top;
    }

    .ft-page-category__code {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-family: Consolas, Monaco, monospace;
        font-size: 12px;
        max-width: 100%;
        overflow-wrap: anywhere;
    }

    .ft-page-category__meta {
        margin-top: 8px;
        color: #64748b;
        line-height: 1.8;
        overflow-wrap: anywhere;
    }

    .ft-page-category__badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #f1f5f9;
        color: #334155;
    }

    .ft-page-category__badge--success {
        background: #dcfce7;
        color: #15803d;
    }

    .ft-page-category__badge--danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    .ft-page-category__actions .btn {
        border-radius: 999px;
        font-weight: 600;
    }

    .ft-page-category__empty {
        padding: 36px 20px !important;
        text-align: center;
        color: #94a3b8;
    }
</style>
<body>
@include(moduleAdminTemplate($moduleName)."public.nav")
<div class="page-container">
    <div class="page-content">
        @include(moduleAdminTemplate($moduleName)."public.left")
        <div class="content-wrapper">
            <div class="content" style="margin-top: 1rem;">
                @include(moduleAdminTemplate($moduleName)."public.crumb",['breadcrumb'=>[$pageData['title'],$pageData['subtitle']]])

                <div class="ft-page-category">
                    <div class="alert alert-info alert-styled-left ft-page-category__note">
                        <span>{{ getTranslateByKey('ft_pc_note') }}</span>
                    </div>

                    <div class="ft-page-category__panel">
                        <div class="ft-page-category__toolbar">
                            <div>
                                <h3 class="ft-page-category__title">{{ getTranslateByKey('ft_pc_title') }}</h3>
                                <p class="ft-page-category__desc">{{ getTranslateByKey('ft_pc_desc') }}</p>
                            </div>
                            <div>
                                <a href="{{url('admin/formtools/pageCategoryAdd')}}" class="btn btn-info btn-sm">{{ getTranslateByKey('ft_pc_add') }}</a>
                                <a href="{{url('admin/formtools/pageList')}}" class="btn btn-default btn-sm">{{ getTranslateByKey('ft_pc_back_list') }}</a>
                            </div>
                        </div>

                        <div class="table-responsive ft-page-category__table-wrap">
                            <table class="table ft-page-category__table m-b-none">
                                <thead>
                                <tr>
                                    <th>{{ getTranslateByKey('ft_pc_th_info') }}</th>
                                    <th>{{ getTranslateByKey('ft_pc_th_status_sort') }}</th>
                                    <th>{{ getTranslateByKey('ft_pc_th_pages') }}</th>
                                    <th>{{ getTranslateByKey('ft_th_time') }}</th>
                                    <th>{{ getTranslateByKey('operation') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($pageData['datas'] as $category)
                                    <tr>
                                        <td>
                                            <div><strong>{{$category->name}}</strong></div>
                                            <div class="ft-page-category__meta">{{ getTranslateByKey('ft_pc_identification') }} <span class="ft-page-category__code">{{$category->identification}}</span></div>
                                            @if($category->remark)
                                                <div class="ft-page-category__meta">{{ getTranslateByKey('ft_pc_remark_with', 'admin', ['name' => $category->remark]) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div><span class="ft-page-category__badge {{$category->status ? 'ft-page-category__badge--success' : 'ft-page-category__badge--danger'}}">{{$category->status ? getTranslateByKey('ft_api_enabled') : getTranslateByKey('ft_api_global_disabled')}}</span></div>
                                            <div class="ft-page-category__meta">{{ getTranslateByKey('ft_sort_with', 'admin', ['name' => $category->sort]) }}</div>
                                        </td>
                                        <td>
                                            <div class="ft-page-category__meta">{{ getTranslateByKey('ft_pc_pages_with', 'admin', ['count' => (int) ($pageCountMap[$category->id] ?? 0)]) }}</div>
                                        </td>
                                        <td>
                                            <div class="ft-page-category__meta">{{ getTranslateByKey('ft_created_at_with', 'admin', ['name' => $category->created_at ?: '-']) }}</div>
                                            <div class="ft-page-category__meta">{{ getTranslateByKey('ft_updated_at_with', 'admin', ['name' => $category->updated_at ?: '-']) }}</div>
                                        </td>
                                        <td class="ft-page-category__actions">
                                            <a href="{{url('admin/formtools/pageCategoryEdit?id='.$category->id)}}" class="btn btn-success btn-xs">{{ getTranslateByKey('common_edit') }}</a>
                                            <a href="#" onclick="del('{{url('admin/formtools/pageCategoryDelete?id='.$category->id)}}');return false;" class="btn btn-danger btn-xs">{{ getTranslateByKey('common_delete') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="ft-page-category__empty">{{ getTranslateByKey('ft_pc_empty') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 text-right text-center-xs" style="margin-top: 18px;">
                    <?php
                        $qs = $_GET ?: [];
                        unset($qs['page']);
                    ?>
                    {{ $pageData['datas']->appends($qs)->links($moduleViewName.'::admin.public.pagination',["data"=>$pageData['datas']]) }}
                </div>

                @include(moduleAdminTemplate($moduleName)."public.footer")
            </div>
        </div>
    </div>
</div>
@include(moduleAdminTemplate($moduleName)."public.js")
</body>
