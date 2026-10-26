{{-- 相册瀑布流模板：CSS 多列瀑布 + 点击灯箱，适合作品集、图库、实景照片 --}}
@php
    $access = $param['model'] ?? '';
    $map = theme_map($access);
    $pageNum = (int) data_get($model, 'home_config.page_num', $model['page_num'] ?? 0);
    $pagePosition = data_get($model, 'home_config.list_page_template', $model['list_page_template'] ?? 'center');
    $columns = (int) theme_setting('gallery_columns', 3);
    $columns = $columns >= 2 && $columns <= 5 ? $columns : 3;
@endphp
<div id="content">
    <div class="container">
        <div class="mx-list-shell">
            @themeInclude('public.listHero', ['model' => $model, 'listContext' => $listContext ?? []])

            <div class="mx-list-panel">
                <div class="mx-gallery" style="--mx-gallery-columns: {{ $columns }};">
                    @forelse($data as $row)
                        @php($it = theme_record($row, $access, $map))
                        @if($it['gallery'] === [])
                            @continue
                        @endif
                        <figure class="mx-gallery__item" data-mx-lightbox-group="gallery-{{ $access }}">
                            @foreach($it['gallery'] as $index => $image)
                                <a href="{{ $image }}"
                                   class="mx-gallery__link @if($index > 0) is-hidden @endif"
                                   data-mx-lightbox
                                   data-caption="{{ $it['title'] }}">
                                    <img src="{{ $image }}" alt="{{ $it['title'] }}" loading="lazy">
                                    @if($index === 0 && count($it['gallery']) > 1)
                                        <span class="mx-gallery__count"><i class="fa fa-th"></i> {{ count($it['gallery']) }}</span>
                                    @endif
                                </a>
                            @endforeach
                            <figcaption class="mx-gallery__caption">
                                @if($it['link'] !== '')
                                    <a href="{{ $it['link'] }}">{{ $it['title'] !== '' ? $it['title'] : ('相册 #' . $it['id']) }}</a>
                                @else
                                    {{ $it['title'] }}
                                @endif
                                @if($it['summary'] !== '')
                                    <span>{{ \Illuminate\Support\Str::limit($it['summary'], 40) }}</span>
                                @endif
                            </figcaption>
                        </figure>
                    @empty
                        <div class="mx-empty mx-list-empty">当前还没有相册内容，请先在后台新增图片数据。</div>
                    @endforelse
                </div>
            </div>

            @if ($pageNum > 0 && ($data_source ?? 'local') === 'local' && is_object($data) && method_exists($data, 'links'))
                {{ $data->appends(request()->except('page'))->links(theme_view('public.pagination'), ['data' => ['side_num' => 2, 'page_position' => $pagePosition]]) }}
            @endif
        </div>
    </div>
</div>
