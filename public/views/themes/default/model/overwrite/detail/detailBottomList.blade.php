<div id="content">
    <div class="container">
        @php($record = frontendRecordData($detailRecord ?? $data ?? []))
        <div class="mx-detail-shell">
            @themeInclude('public.detailHero', [
                'model' => $model,
                'data' => $data,
                'detailRecord' => $record,
            ])

            <article class="mx-detail-main">
                @themeInclude('public.detailContent', [
                    'detailRecord' => $record,
                    'detailFields' => $model['frontend_schema']['detail'] ?? [],
                    'model' => $model,
                ])
                @themeInclude('public.articleNavigation')
            </article>

            <section class="mx-detail-section">
                <h3 class="mx-detail-section__title">{{ themeTrans('article.related') }}</h3>
                <div class="mx-detail-related">
                    @foreach($list as $key => $l)
                        <div class="mx-detail-related__item">
                            <span class="mx-detail-related__index">{{ $key + 1 }}</span>
                            <div class="mx-detail-related__content">
                                <a href="{{ model_detail_url($param['model'], $l['id']) }}">{{ $l['title'] ?? ($l['name'] ?? themeTrans('common.content_no', ['id' => $l['id']])) }}</a>
                                @if(!empty($l['created_at']))
                                    <span class="mx-detail-related__meta">{{ $l['created_at'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>

