@php
    $record = frontendRecordData($detailRecord ?? $data ?? []);
    $detailFields = $model['frontend_schema']['detail'] ?? [];
    $title = $record['title'] ?? ($record['name'] ?? ($model['name'] ?? themeTrans('detail.default_title')));
@endphp

<div id="content">
    <div class="container">
        <div class="mx-detail-shell">
            @themeInclude('public.detailHero', [
                'model' => $model,
                'data' => $data,
                'detailRecord' => $record,
                'detailTitle' => $title,
            ])

            <article class="mx-detail-main">
                @themeInclude('public.detailContent', [
                    'detailRecord' => $record,
                    'detailFields' => $detailFields,
                    'model' => $model,
                ])

                @themeInclude('public.articleNavigation')
            </article>
        </div>
    </div>
</div>
