@include(moduleAdminTemplate($moduleName)."public.header")
@php
    $model = $pageData['currentModel'];
    $stats = $pageData['statistics'] ?? [];
    $statusCounts = $stats['status_counts'] ?? [];
    $statusMetaMap = [
        '1' => ['label' => getTranslateByKey('ft_api_pass'), 'class' => 'ft-stat-badge ft-stat-badge--approve'],
        '0' => ['label' => getTranslateByKey('ft_stat_pending'), 'class' => 'ft-stat-badge ft-stat-badge--pending'],
        '2' => ['label' => getTranslateByKey('ft_api_offline'), 'class' => 'ft-stat-badge ft-stat-badge--offline'],
    ];
    $dataSource = $stats['data_source'] ?? 'local';
    $contentManageUrl = url('admin/formtools/model?moduleName=' . ($pageData['moduleName'] ?? 'Formtools') . '&action=List&model=' . $model->identification);
    $fieldListUrl = url('admin/formtools/fieldList?id=' . $model->id);
    $modelEditUrl = url('admin/formtools/modelEdit?id=' . $model->id);
    $previewUrl = $model->access_identification ? model_list_url($model->access_identification) : '';
@endphp
<style>
    .ft-stat-page {
        display: grid;
        gap: 18px;
    }

    .ft-stat-note {
        margin-bottom: 0;
        border-radius: 14px;
        border: 1px solid #dbeafe;
        background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
        color: #1e3a8a;
        box-shadow: 0 10px 26px rgba(30, 64, 175, 0.08);
    }

    .ft-stat-overview {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .ft-stat-card,
    .ft-stat-panel,
    .ft-stat-ranking {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
    }

    .ft-stat-card {
        padding: 18px 20px;
    }

    .ft-stat-card__label {
        margin: 0 0 8px;
        font-size: 12px;
        color: #64748b;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .ft-stat-card__value {
        margin: 0;
        font-size: 30px;
        line-height: 1;
        font-weight: 700;
        color: #0f172a;
    }

    .ft-stat-card__desc {
        margin-top: 8px;
        font-size: 13px;
        color: #94a3b8;
        line-height: 1.7;
    }

    .ft-stat-panel__header,
    .ft-stat-ranking__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 20px 22px;
        border-bottom: 1px solid #edf2f7;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .ft-stat-panel__title,
    .ft-stat-ranking__title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .ft-stat-panel__desc,
    .ft-stat-ranking__desc {
        margin: 6px 0 0;
        color: #64748b;
        line-height: 1.8;
    }

    .ft-stat-panel__body {
        padding: 20px 22px 22px;
    }

    .ft-stat-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .ft-stat-meta__item {
        padding: 14px 16px;
        border-radius: 14px;
        background: #f8fafc;
        color: #475569;
        line-height: 1.8;
    }

    .ft-stat-code {
        display: inline-block;
        margin-left: 6px;
        padding: 2px 8px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-family: Consolas, Monaco, monospace;
        font-size: 12px;
    }

    .ft-stat-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ft-stat-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .ft-stat-badge--approve {
        background: #dcfce7;
        color: #166534;
    }

    .ft-stat-badge--pending {
        background: #fef3c7;
        color: #92400e;
    }

    .ft-stat-badge--offline {
        background: #fee2e2;
        color: #991b1b;
    }

    .ft-stat-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .ft-stat-actions .btn {
        border-radius: 999px;
        font-weight: 600;
    }

    .ft-stat-rankings {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .ft-stat-list {
        padding: 16px 22px 22px;
    }

    .ft-stat-list__row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #edf2f7;
    }

    .ft-stat-list__row:last-child {
        border-bottom: 0;
    }

    .ft-stat-list__title {
        color: #0f172a;
        font-weight: 600;
        line-height: 1.6;
    }

    .ft-stat-list__sub {
        margin-top: 4px;
        color: #94a3b8;
        font-size: 12px;
    }

    .ft-stat-list__value {
        min-width: 72px;
        text-align: right;
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
    }

    .ft-stat-empty {
        padding: 22px;
        color: #94a3b8;
        text-align: center;
    }

    .ft-stat-publisher-table {
        margin: 0;
    }

    .ft-stat-publisher-table > thead > tr > th {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        background: #f8fafc;
    }

    .ft-stat-publisher-table > tbody > tr > td {
        padding: 14px 16px;
        border-top: 1px solid #edf2f7;
        vertical-align: middle;
    }

    @media (max-width: 1199px) {
        .ft-stat-overview,
        .ft-stat-rankings {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991px) {
        .ft-stat-panel__header,
        .ft-stat-ranking__header {
            flex-direction: column;
        }

        .ft-stat-meta {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .ft-stat-overview,
        .ft-stat-rankings {
            grid-template-columns: 1fr;
        }
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

                <div class="ft-stat-page">
                    <div class="alert alert-info alert-styled-left ft-stat-note">
                        <span>{{ getTranslateByKey('ft_stat_note') }}</span>
                    </div>

                    <div class="ft-stat-overview">
                        @foreach(($stats['cards'] ?? []) as $card)
                            <div class="ft-stat-card">
                                <p class="ft-stat-card__label">{{$card['label']}}</p>
                                <p class="ft-stat-card__value">{{number_format((int) ($card['value'] ?? 0))}}</p>
                                <div class="ft-stat-card__desc">{{$card['desc']}}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="ft-stat-panel">
                        <div class="ft-stat-panel__header">
                            <div>
                                <h3 class="ft-stat-panel__title">{{ getTranslateByKey('ft_stat_title_with', 'admin', ['name' => $model->name]) }}</h3>
                                <p class="ft-stat-panel__desc">{{ getTranslateByKey('ft_stat_panel_desc') }}</p>
                            </div>
                            <div class="ft-stat-actions">
                                <a href="{{$modelEditUrl}}" class="btn btn-success btn-sm">{{ getTranslateByKey('ft_model_config') }}</a>
                                <a href="{{$fieldListUrl}}" class="btn btn-info btn-sm">{{ getTranslateByKey('ft_field_manage') }}</a>
                                <a href="{{$contentManageUrl}}" class="btn btn-primary btn-sm">{{ getTranslateByKey('ft_content_manage') }}</a>
                                @if($previewUrl)
                                    <a href="{{$previewUrl}}" class="btn btn-default btn-sm" target="_blank">{{ getTranslateByKey('ft_home_preview') }}</a>
                                @endif
                            </div>
                        </div>
                        <div class="ft-stat-panel__body">
                            <div class="ft-stat-meta">
                                <div class="ft-stat-meta__item">
                                    {{ getTranslateByKey('ft_stat_current_model') }} <span class="ft-stat-code">{{$model->identification}}</span><br>
                                    {{ getTranslateByKey('ft_stat_table') }} <span class="ft-stat-code">{{$pageData['tableName']}}</span><br>
                                    {{ getTranslateByKey('ft_stat_data_source') }} <span class="ft-stat-code">{{$dataSource === 'api' ? 'api' : 'local'}}</span>
                                </div>
                                <div class="ft-stat-meta__item">
                                    @if($dataSource === 'api')
                                        {{ getTranslateByKey('ft_stat_api_note') }}
                                    @else
                                        {{ getTranslateByKey('ft_stat_local_note') }}
                                    @endif
                                </div>
                            </div>

                            <div class="ft-stat-badges">
                                @foreach($statusMetaMap as $status => $meta)
                                    <span class="{{$meta['class']}}">{{$meta['label']}} {{$statusCounts[$status] ?? 0}}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="ft-stat-rankings">
                        <div class="ft-stat-ranking">
                            <div class="ft-stat-ranking__header">
                                <div>
                                    <h3 class="ft-stat-ranking__title">{{ getTranslateByKey('ft_stat_access_rank') }}</h3>
                                    <p class="ft-stat-ranking__desc">{{ getTranslateByKey('ft_stat_access_rank_desc') }}</p>
                                </div>
                            </div>
                            <div class="ft-stat-list">
                                @forelse(($stats['top_access'] ?? []) as $row)
                                    <div class="ft-stat-list__row">
                                        <div>
                                            <div class="ft-stat-list__title">{{$row['title']}}</div>
                                            <div class="ft-stat-list__sub">ID #{{$row['id']}}</div>
                                        </div>
                                        <div class="ft-stat-list__value">{{$row['value']}}</div>
                                    </div>
                                @empty
                                    <div class="ft-stat-empty">{{ getTranslateByKey('ft_stat_no_access_data') }}</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="ft-stat-ranking">
                            <div class="ft-stat-ranking__header">
                                <div>
                                    <h3 class="ft-stat-ranking__title">{{ getTranslateByKey('ft_stat_good_rank') }}</h3>
                                    <p class="ft-stat-ranking__desc">{{ getTranslateByKey('ft_stat_good_rank_desc') }}</p>
                                </div>
                            </div>
                            <div class="ft-stat-list">
                                @forelse(($stats['top_good'] ?? []) as $row)
                                    <div class="ft-stat-list__row">
                                        <div>
                                            <div class="ft-stat-list__title">{{$row['title']}}</div>
                                            <div class="ft-stat-list__sub">ID #{{$row['id']}}</div>
                                        </div>
                                        <div class="ft-stat-list__value">{{$row['value']}}</div>
                                    </div>
                                @empty
                                    <div class="ft-stat-empty">{{ getTranslateByKey('ft_stat_no_good_data') }}</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="ft-stat-ranking">
                            <div class="ft-stat-ranking__header">
                                <div>
                                    <h3 class="ft-stat-ranking__title">{{ getTranslateByKey('ft_stat_download_rank') }}</h3>
                                    <p class="ft-stat-ranking__desc">{{ getTranslateByKey('ft_stat_download_rank_desc') }}</p>
                                </div>
                            </div>
                            <div class="ft-stat-list">
                                @forelse(($stats['top_download'] ?? []) as $row)
                                    <div class="ft-stat-list__row">
                                        <div>
                                            <div class="ft-stat-list__title">{{$row['title']}}</div>
                                            <div class="ft-stat-list__sub">ID #{{$row['id']}}</div>
                                        </div>
                                        <div class="ft-stat-list__value">{{$row['value']}}</div>
                                    </div>
                                @empty
                                    <div class="ft-stat-empty">{{ getTranslateByKey('ft_stat_no_download_data') }}</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="ft-stat-ranking">
                            <div class="ft-stat-ranking__header">
                                <div>
                                    <h3 class="ft-stat-ranking__title">{{ getTranslateByKey('ft_stat_publisher_rank') }}</h3>
                                    <p class="ft-stat-ranking__desc">{{ getTranslateByKey('ft_stat_publisher_rank_desc') }}</p>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table ft-stat-publisher-table">
                                    <thead>
                                    <tr>
                                        <th>UID</th>
                                        <th>{{ getTranslateByKey('ft_stat_content_count') }}</th>
                                        <th>{{ getTranslateByKey('ft_stat_access') }}</th>
                                        <th>{{ getTranslateByKey('ft_stat_good') }}</th>
                                        <th>{{ getTranslateByKey('ft_stat_download') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse(($stats['publisher_ranking'] ?? []) as $publisher)
                                        <tr>
                                            <td>{{$publisher['uid']}}</td>
                                            <td>{{$publisher['content_count']}}</td>
                                            <td>{{$publisher['access_total']}}</td>
                                            <td>{{$publisher['good_total']}}</td>
                                            <td>{{$publisher['download_total']}}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="ft-stat-empty">{{ getTranslateByKey('ft_stat_no_publisher_data') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                @include(moduleAdminTemplate($moduleName)."public.footer")
            </div>
        </div>
    </div>
</div>

@include(moduleAdminTemplate($moduleName)."public.js")
</body>
</html>
