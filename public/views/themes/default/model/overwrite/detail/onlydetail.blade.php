@php
    $record = frontendRecordData($detailRecord ?? $data ?? []);
    $detailFields = $model['frontend_schema']['detail'] ?? [];
@endphp

<div id="content">
    <div class="container">
        <div class="mx-detail-shell">
            @themeInclude('public.detailHero', [
                'model' => $model,
                'data' => $data,
                'detailRecord' => $record,
            ])

            <article class="mx-detail-main">
                @themeInclude('public.detailContent', [
                    'detailRecord' => $record,
                    'detailFields' => $detailFields,
                    'model' => $model,
                ])
            </article>
        </div>
    </div>
</div>
