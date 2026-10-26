{{-- 视频网格模板：封面带播放按钮，点击弹窗播放（支持 mp4 直链与主流平台 iframe） --}}
@php
    $access = $param['model'] ?? '';
    $map = theme_map($access);
    $pageNum = (int) data_get($model, 'home_config.page_num', $model['page_num'] ?? 0);
    $pagePosition = data_get($model, 'home_config.list_page_template', $model['list_page_template'] ?? 'center');
@endphp
<div id="content">
    <div class="container">
        <div class="mx-list-shell">
            @themeInclude('public.listHero', ['model' => $model, 'listContext' => $listContext ?? []])

            <div class="mx-list-panel">
                <div class="mx-video-grid">
                    @forelse($data as $row)
                        @php($it = theme_record($row, $access, $map))
                        <article class="mx-video-card">
                            <button type="button"
                                    class="mx-video-card__media"
                                    data-mx-video
                                    data-video-type="{{ $it['video_type'] }}"
                                    data-video-src="{{ $it['video_url'] }}"
                                    data-video-title="{{ $it['title'] }}"
                                    @if($it['video_url'] === '') disabled @endif>
                                @if($it['cover_url'] !== '')
                                    <img src="{{ $it['cover_url'] }}" alt="{{ $it['title'] }}" loading="lazy">
                                @else
                                    <span class="mx-product__placeholder"><i class="fa fa-film"></i></span>
                                @endif
                                <span class="mx-video-play"><i class="fa fa-play"></i></span>
                            </button>
                            <div class="mx-video-card__body">
                                <h3 class="mx-video-card__title">
                                    @if($it['link'] !== '')
                                        <a href="{{ $it['link'] }}">{{ $it['title'] !== '' ? $it['title'] : ('视频 #' . $it['id']) }}</a>
                                    @else
                                        {{ $it['title'] !== '' ? $it['title'] : ('视频 #' . $it['id']) }}
                                    @endif
                                </h3>
                                @if($it['summary'] !== '')
                                    <p class="mx-video-card__desc">{{ \Illuminate\Support\Str::limit($it['summary'], 60) }}</p>
                                @endif
                                @if($it['date'] !== '')
                                    <div class="mx-video-card__meta"><i class="fa fa-clock-o"></i> {{ $it['date'] }}</div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="mx-empty mx-list-empty">当前还没有视频内容，请先在后台新增视频数据。</div>
                    @endforelse
                </div>
            </div>

            @if ($pageNum > 0 && ($data_source ?? 'local') === 'local' && is_object($data) && method_exists($data, 'links'))
                {{ $data->appends(request()->except('page'))->links(theme_view('public.pagination'), ['data' => ['side_num' => 2, 'page_position' => $pagePosition]]) }}
            @endif
        </div>
    </div>
</div>
