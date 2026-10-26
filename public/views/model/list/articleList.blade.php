{{-- 资讯图文列表模板：左封面右正文摘要，标准新闻 / 博客 / 公告列表 --}}
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
                <div class="mx-article-list">
                    @forelse($data as $row)
                        @php($it = theme_record($row, $access, $map))
                        <article class="mx-article-card @if($it['cover_url'] === '') mx-article-card--plain @endif">
                            @if($it['cover_url'] !== '')
                                <a href="{{ $it['link'] }}" class="mx-article-card__thumb">
                                    <img src="{{ $it['cover_url'] }}" alt="{{ $it['title'] }}" loading="lazy">
                                </a>
                            @endif
                            <div class="mx-article-card__body">
                                <h3 class="mx-article-card__title">
                                    <a href="{{ $it['link'] }}">{{ $it['title'] !== '' ? $it['title'] : ('内容 #' . $it['id']) }}</a>
                                </h3>
                                @if($it['summary'] !== '')
                                    <p class="mx-article-card__desc">{{ \Illuminate\Support\Str::limit($it['summary'], 110) }}</p>
                                @endif
                                <div class="mx-article-card__meta">
                                    @if($it['date'] !== '')
                                        <span><i class="fa fa-clock-o"></i> {{ $it['date'] }}</span>
                                    @endif
                                    @if(!empty($it['raw']['author']))
                                        <span><i class="fa fa-user-o"></i> {{ $it['raw']['author'] }}</span>
                                    @endif
                                    @if(!empty($it['raw']['cate_name']))
                                        <span><i class="fa fa-folder-o"></i> {{ $it['raw']['cate_name'] }}</span>
                                    @endif
                                    @if(!empty($it['raw']['access_count']))
                                        <span><i class="fa fa-eye"></i> {{ (int) $it['raw']['access_count'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="mx-empty mx-list-empty">当前模型还没有可展示的数据，请先在后台内容管理中新增内容。</div>
                    @endforelse
                </div>
            </div>

            @if ($pageNum > 0 && ($data_source ?? 'local') === 'local' && is_object($data) && method_exists($data, 'links'))
                {{ $data->appends(request()->except('page'))->links(theme_view('public.pagination'), ['data' => ['side_num' => 2, 'page_position' => $pagePosition]]) }}
            @endif
        </div>
    </div>
</div>
