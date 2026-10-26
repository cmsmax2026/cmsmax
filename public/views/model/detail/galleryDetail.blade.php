{{-- 相册详情模板：整页图集 + 灯箱，适合作品集、实景图、活动照片详情 --}}
@php
    $access = $param['model'] ?? '';
    $map = theme_map($access);
    $record = theme_record($detailRecord ?? $data ?? [], $access, $map);
    $gallery = $record['gallery'];
    $detailFields = $model['frontend_schema']['detail'] ?? [];
@endphp

<div id="content">
    <div class="container">
        <div class="mx-detail-shell">
            <div class="mx-detail-hero">
                <div class="mx-detail-hero__main">
                    <div class="mx-detail-hero__eyebrow">Gallery</div>
                    <h1 class="mx-detail-hero__title">{{ $record['title'] !== '' ? $record['title'] : ($model['name'] ?? '相册详情') }}</h1>
                    @if($record['summary'] !== '')
                        <p class="mx-detail-hero__description">{{ $record['summary'] }}</p>
                    @endif
                    <div class="mx-detail-hero__meta">
                        @if($record['date'] !== '')
                            <span class="mx-detail-meta-pill"><i class="fa fa-clock-o"></i><em>{{ $record['date'] }}</em></span>
                        @endif
                        <span class="mx-detail-meta-pill"><i class="fa fa-picture-o"></i><em>{{ count($gallery) }} 张图片</em></span>
                    </div>
                    <div class="mx-detail-hero__actions">
                        <a class="mx-detail-action-btn mx-detail-action-btn--ghost" href="{{ model_list_url($access) }}">
                            <i class="fa fa-angle-left"></i>
                            <span>返回列表</span>
                        </a>
                    </div>
                </div>
            </div>

            <article class="mx-detail-main">
                @if($gallery !== [])
                    <div class="mx-gallery mx-gallery--detail" data-mx-lightbox-group="gallery-{{ $access }}-{{ $record['id'] }}">
                        @foreach($gallery as $image)
                            <a href="{{ $image }}" class="mx-gallery__item" data-mx-lightbox data-caption="{{ $record['title'] }}">
                                <img src="{{ $image }}" alt="{{ $record['title'] }}" loading="lazy">
                                <span class="mx-gallery__zoom"><i class="fa fa-search-plus"></i></span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="mx-empty">该相册还没有上传图片。</div>
                @endif

                @if($record['content'] !== '')
                    <div class="article-content mx-detail-content">{!! $record['content'] !!}</div>
                @else
                    @themeInclude('public.detailContent', [
                        'detailRecord' => $record['raw'],
                        'detailFields' => $detailFields,
                        'model' => $model,
                        'detailInteractions' => $detailInteractions ?? [],
                    ])
                @endif

                @themeInclude('public.articleNavigation')
            </article>
        </div>
    </div>
</div>
