{{-- {mx:list tpl="product" /} 的商品卡片网格
     可用变量：$rows / $items / $result / $attrs / $map --}}
@php($cols = max(2, min(6, (int) ($attrs['cols'] ?? 4))))
<div class="mx-product-grid" style="grid-template-columns: repeat({{ $cols }}, minmax(0, 1fr));">
    @foreach($rows as $row)
        @php($it = theme_record($row['raw'] ?? $row, (string) ($result['access'] ?? ''), $map))
        @php($title = $it['title'] !== '' ? $it['title'] : themeTrans('home.product_no', ['id' => $it['id']]))
        <article class="mx-product">
            <a href="{{ $it['link'] }}" class="mx-product__media">
                @if($it['cover_url'] !== '')
                    <img src="{{ $it['cover_url'] }}" alt="{{ $title }}" loading="lazy">
                @else
                    <span class="mx-product__placeholder"><i class="fa fa-picture-o"></i></span>
                @endif
                @if($it['stock'] === 0)
                    <span class="mx-product__badge mx-product__badge--off">{{ themeTrans('common.sold_out') }}</span>
                @elseif($it['origin_price'] !== '' && $it['price'] !== '')
                    <span class="mx-product__badge">{{ themeTrans('common.on_sale') }}</span>
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
                <h3 class="mx-product__title"><a href="{{ $it['link'] }}">{{ $title }}</a></h3>
                @if($it['spec'] !== '')
                    <p class="mx-product__spec">{{ $it['spec'] }}</p>
                @endif
                <div class="mx-product__price">
                    @if($it['price'] !== '')
                        <strong class="mx-price">{{ $it['price'] }}</strong>
                        @if($it['origin_price'] !== '')
                            <del class="mx-price-origin">{{ $it['origin_price'] }}</del>
                        @endif
                    @else
                        <strong class="mx-price mx-price--quote">{{ themeTrans('common.price_quote') }}</strong>
                    @endif
                </div>
                <a class="mx-product__action" href="{{ $it['link'] }}">{{ themeTrans('common.view_detail') }}</a>
            </div>
        </article>
    @endforeach
</div>
