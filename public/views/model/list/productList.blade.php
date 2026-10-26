{{-- 商品列表模板：左图右文的横向大卡，信息量比网格更大，适合商品详情页引流、课程列表 --}}
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
                <div class="mx-product-rows">
                    @forelse($data as $row)
                        @php($it = theme_record($row, $access, $map))
                        <article class="mx-product-row">
                            <a href="{{ $it['link'] }}" class="mx-product-row__media">
                                @if($it['cover_url'] !== '')
                                    <img src="{{ $it['cover_url'] }}" alt="{{ $it['title'] }}" loading="lazy">
                                @else
                                    <span class="mx-product__placeholder"><i class="fa fa-picture-o"></i></span>
                                @endif
                            </a>
                            <div class="mx-product-row__body">
                                <div class="mx-product-row__head">
                                    <h3 class="mx-product-row__title">
                                        <a href="{{ $it['link'] }}">{{ $it['title'] !== '' ? $it['title'] : ('商品 #' . $it['id']) }}</a>
                                    </h3>
                                    @if($it['tags'] !== [])
                                        <div class="mx-tag-row">
                                            @foreach(array_slice($it['tags'], 0, 3) as $tag)
                                                <span class="mx-tag">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @if($it['spec'] !== '')
                                    <p class="mx-product__spec">{{ $it['spec'] }}</p>
                                @endif
                                @if($it['summary'] !== '')
                                    <p class="mx-product__summary">{{ \Illuminate\Support\Str::limit($it['summary'], 120) }}</p>
                                @endif
                                <div class="mx-product-row__foot">
                                    <div class="mx-product__price">
                                        @if($it['price'] !== '')
                                            <strong class="mx-price">{{ $it['price'] }}</strong>
                                            @if($it['origin_price'] !== '')
                                                <del class="mx-price-origin">{{ $it['origin_price'] }}</del>
                                            @endif
                                        @else
                                            <strong class="mx-price mx-price--quote">面议</strong>
                                        @endif
                                    </div>
                                    <div class="mx-product-row__actions">
                                        @if($it['stock'] !== null)
                                            <span class="mx-product__stock">库存 {{ $it['stock'] }}</span>
                                        @endif
                                        <a class="mx-product__action" href="{{ $it['link'] }}">了解详情</a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="mx-empty mx-list-empty">当前还没有商品数据，请先在后台内容管理中新增内容。</div>
                    @endforelse
                </div>
            </div>

            @if ($pageNum > 0 && ($data_source ?? 'local') === 'local' && is_object($data) && method_exists($data, 'links'))
                {{ $data->appends(request()->except('page'))->links(theme_view('public.pagination'), ['data' => ['side_num' => 2, 'page_position' => $pagePosition]]) }}
            @endif
        </div>
    </div>
</div>
