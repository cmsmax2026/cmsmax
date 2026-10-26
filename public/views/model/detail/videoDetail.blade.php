{{-- 视频详情模板：顶部播放器 + 正文 + 相关推荐，适合视频、课程、直播回放详情 --}}
@php
    $access = $param['model'] ?? '';
    $map = theme_map($access);
    $record = theme_record($detailRecord ?? $data ?? [], $access, $map);
    $detailFields = $model['frontend_schema']['detail'] ?? [];
    $relatedList = array_values(array_filter((array) ($list ?? []), static function ($item) use ($record) {
        return (int) (is_array($item) ? ($item['id'] ?? 0) : ($item->id ?? 0)) !== $record['id'];
    }));
@endphp

<div id="content">
    <div class="container">
        <div class="mx-detail-shell">
            <div class="mx-video-detail">
                <div class="mx-video-detail__player">
                    @if($record['video_type'] === 'mp4' && $record['video_url'] !== '')
                        <video controls preload="metadata" playsinline poster="{{ $record['cover_url'] }}">
                            <source src="{{ $record['video_url'] }}">
                            您的浏览器不支持在线播放该视频。
                        </video>
                    @elseif($record['video_type'] === 'iframe' && $record['video_url'] !== '')
                        <iframe src="{{ $record['video_url'] }}" allowfullscreen frameborder="0" title="{{ $record['title'] }}"></iframe>
                    @else
                        <div class="mx-video-detail__empty">
                            @if($record['cover_url'] !== '')
                                <img src="{{ $record['cover_url'] }}" alt="{{ $record['title'] }}">
                            @endif
                            <span><i class="fa fa-play-circle"></i> 该内容暂未提供可播放的视频地址</span>
                        </div>
                    @endif
                </div>

                <div class="mx-video-detail__head">
                    <h1 class="mx-video-detail__title">{{ $record['title'] !== '' ? $record['title'] : ($model['name'] ?? '视频详情') }}</h1>
                    <div class="mx-video-detail__meta">
                        @if($record['date'] !== '')
                            <span><i class="fa fa-clock-o"></i> {{ $record['date'] }}</span>
                        @endif
                        @if(!empty($record['raw']['access_count']))
                            <span><i class="fa fa-eye"></i> {{ (int) $record['raw']['access_count'] }} 次播放</span>
                        @endif
                        @if(!empty($record['raw']['author']))
                            <span><i class="fa fa-user-o"></i> {{ $record['raw']['author'] }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <article class="mx-detail-main">
                @themeInclude('public.detailContent', [
                    'detailRecord' => $record['raw'],
                    'detailFields' => $detailFields,
                    'model' => $model,
                    'detailInteractions' => $detailInteractions ?? [],
                ])

                @if($relatedList !== [])
                    <section class="mx-detail-section">
                        <h2 class="mx-detail-section__title">相关视频</h2>
                        <div class="mx-video-grid">
                            @foreach(array_slice($relatedList, 0, 6) as $row)
                                @php($rel = theme_record($row, $access, $map))
                                <article class="mx-video-card">
                                    <button type="button"
                                            class="mx-video-card__media"
                                            data-mx-video
                                            data-video-type="{{ $rel['video_type'] }}"
                                            data-video-src="{{ $rel['video_url'] }}"
                                            data-video-title="{{ $rel['title'] }}"
                                            @if($rel['video_url'] === '') disabled @endif>
                                        @if($rel['cover_url'] !== '')
                                            <img src="{{ $rel['cover_url'] }}" alt="{{ $rel['title'] }}" loading="lazy">
                                        @else
                                            <span class="mx-product__placeholder"><i class="fa fa-film"></i></span>
                                        @endif
                                        <span class="mx-video-play"><i class="fa fa-play"></i></span>
                                    </button>
                                    <div class="mx-video-card__body">
                                        <h3 class="mx-video-card__title">
                                            <a href="{{ $rel['link'] }}">{{ $rel['title'] !== '' ? $rel['title'] : ('视频 #' . $rel['id']) }}</a>
                                        </h3>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @themeInclude('public.articleNavigation')
            </article>
        </div>
    </div>
</div>
