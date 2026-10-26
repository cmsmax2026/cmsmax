{{-- 团队成员模板：头像 + 姓名 + 职位 + 简介 + 联系方式，适合团队、讲师、专家介绍 --}}
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
                <div class="mx-team-grid">
                    @forelse($data as $row)
                        @php($it = theme_record($row, $access, $map))
                        <article class="mx-team-card">
                            <div class="mx-team-card__avatar">
                                @if($it['cover_url'] !== '')
                                    <img src="{{ $it['cover_url'] }}" alt="{{ $it['title'] }}" loading="lazy">
                                @else
                                    <span class="mx-team-card__initial">{{ mb_substr($it['title'], 0, 1) ?: '成' }}</span>
                                @endif
                            </div>
                            <div class="mx-team-card__body">
                                <h3 class="mx-team-card__name">
                                    @if($it['link'] !== '')
                                        <a href="{{ $it['link'] }}">{{ $it['title'] !== '' ? $it['title'] : ('成员 #' . $it['id']) }}</a>
                                    @else
                                        {{ $it['title'] !== '' ? $it['title'] : ('成员 #' . $it['id']) }}
                                    @endif
                                </h3>
                                @if($it['position'] !== '')
                                    <p class="mx-team-card__position">{{ $it['position'] }}</p>
                                @endif
                                @if($it['summary'] !== '')
                                    <p class="mx-team-card__desc">{{ \Illuminate\Support\Str::limit($it['summary'], 68) }}</p>
                                @endif
                                <div class="mx-team-card__contact">
                                    @if($it['phone'] !== '')
                                        <span title="{{ $it['phone'] }}"><i class="fa fa-phone"></i> {{ $it['phone'] }}</span>
                                    @endif
                                    @if($it['email'] !== '')
                                        <a href="mailto:{{ $it['email'] }}"><i class="fa fa-envelope-o"></i> {{ $it['email'] }}</a>
                                    @endif
                                    @if($it['address'] !== '')
                                        <span><i class="fa fa-map-marker"></i> {{ $it['address'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="mx-empty mx-list-empty">当前还没有团队成员数据，请先在后台新增内容。</div>
                    @endforelse
                </div>
            </div>

            @if ($pageNum > 0 && ($data_source ?? 'local') === 'local' && is_object($data) && method_exists($data, 'links'))
                {{ $data->appends(request()->except('page'))->links(theme_view('public.pagination'), ['data' => ['side_num' => 2, 'page_position' => $pagePosition]]) }}
            @endif
        </div>
    </div>
</div>
