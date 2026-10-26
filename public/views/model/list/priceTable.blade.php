{{-- 价格套餐模板：并排对比卡片，支持推荐套餐高亮，适合定价、会员等级、服务套餐 --}}
@php
    $access = $param['model'] ?? '';
    $map = theme_map($access);
    $pageNum = (int) data_get($model, 'home_config.page_num', $model['page_num'] ?? 0);
    $pagePosition = data_get($model, 'home_config.list_page_template', $model['list_page_template'] ?? 'center');
    // recommended 字段（推荐/热门/精选）存在时该套餐高亮
    $recommendKeys = ['recommended', 'recommend', 'is_recommend', 'hot', 'featured'];
@endphp
<div id="content">
    <div class="container">
        <div class="mx-list-shell">
            @themeInclude('public.listHero', ['model' => $model, 'listContext' => $listContext ?? []])

            <div class="mx-list-panel">
                <div class="mx-price-grid">
                    @forelse($data as $row)
                        @php($it = theme_record($row, $access, $map))
                        @php($isRecommended = false)
                        @foreach($recommendKeys as $key)
                            @if(!empty($it['raw'][$key]) && $it['raw'][$key] != '0')
                                @php($isRecommended = true)
                                @break
                            @endif
                        @endforeach
                        <article class="mx-price-card @if($isRecommended) is-recommended @endif">
                            @if($isRecommended)
                                <span class="mx-price-card__ribbon">推荐</span>
                            @endif
                            <h3 class="mx-price-card__name">{{ $it['title'] !== '' ? $it['title'] : ('套餐 #' . $it['id']) }}</h3>
                            @if($it['spec'] !== '')
                                <p class="mx-price-card__spec">{{ $it['spec'] }}</p>
                            @endif
                            <div class="mx-price-card__price">
                                @if($it['price'] !== '')
                                    <strong>{{ $it['price'] }}</strong>
                                    @if($it['origin_price'] !== '')
                                        <del>{{ $it['origin_price'] }}</del>
                                    @endif
                                @else
                                    <strong class="mx-price--quote">面议</strong>
                                @endif
                            </div>
                            @if($it['content'] !== '')
                                <div class="mx-price-card__features">{!! $it['content'] !!}</div>
                            @elseif($it['summary'] !== '')
                                <ul class="mx-price-card__list">
                                    @foreach((array) preg_split('/[\r\n]+/', trim($it['summary'])) as $line)
                                        @continue(trim($line) === '')
                                        <li><i class="fa fa-check"></i> {{ trim($line, " \t\n\r\0\x0B-·•") }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            <a class="mx-price-card__action" href="{{ $it['link'] ?: 'javascript:void(0)' }}">立即选择</a>
                        </article>
                    @empty
                        <div class="mx-empty mx-list-empty">当前还没有套餐数据，请先在后台新增内容。</div>
                    @endforelse
                </div>
            </div>

            @if ($pageNum > 0 && ($data_source ?? 'local') === 'local' && is_object($data) && method_exists($data, 'links'))
                {{ $data->appends(request()->except('page'))->links(theme_view('public.pagination'), ['data' => ['side_num' => 2, 'page_position' => $pagePosition]]) }}
            @endif
        </div>
    </div>
</div>
