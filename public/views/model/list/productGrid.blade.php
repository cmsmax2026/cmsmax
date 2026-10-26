{{-- 商品网格模板：封面 + 标签 + 价格 + 库存，适合商品、套餐、课程、服务报价 --}}
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
                <div class="mx-product-grid">
                    @forelse($data as $row)
                        @php($it = theme_record($row, $access, $map))
                        <article class="mx-product">
                            <a href="{{ $it['link'] }}" class="mx-product__media">
                                @if($it['cover_url'] !== '')
                                    <img src="{{ $it['cover_url'] }}" alt="{{ $it['title'] }}" loading="lazy">
                                @else
                                    <span class="mx-product__placeholder"><i class="fa fa-picture-o"></i></span>
                                @endif
                                @if($it['stock'] === 0)
                                    <span class="mx-product__badge mx-product__badge--off">已售罄</span>
                                @elseif($it['origin_price'] !== '' && $it['price'] !== '')
                                    <span class="mx-product__badge">促销中</span>
                                @endif
                            </a>
                            <div class="mx-product__body">
                                @if($it['tags'] !== [])
                                    <div class="mx-tag-row">
                                        @foreach(array_slice($it['tags'], 0, 3) as $tag)
                                            <span class="mx-tag">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <h3 class="mx-product__title">
                                    <a href="{{ $it['link'] }}">{{ $it['title'] !== '' ? $it['title'] : ('商品 #' . $it['id']) }}</a>
                                </h3>
                                @if($it['spec'] !== '')
                                    <p class="mx-product__spec">{{ $it['spec'] }}</p>
                                @endif
                                @if($it['summary'] !== '')
                                    <p class="mx-product__summary">{{ \Illuminate\Support\Str::limit($it['summary'], 46) }}</p>
                                @endif
                                <div class="mx-product__price">
                                    @if($it['price'] !== '')
                                        <strong class="mx-price">{{ $it['price'] }}</strong>
                                        @if($it['origin_price'] !== '')
                                            <del class="mx-price-origin">{{ $it['origin_price'] }}</del>
                                        @endif
                                    @else
                                        <strong class="mx-price mx-price--quote">面议</strong>
                                    @endif
                                    @if($it['stock'] !== null)
                                        <span class="mx-product__stock">库存 {{ $it['stock'] }}</span>
                                    @endif
                                </div>
                                <a class="mx-product__action" href="{{ $it['link'] }}">查看详情</a>
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
