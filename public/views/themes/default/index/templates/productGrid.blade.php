@php
    $home_config = json_decode($data->home_config, true) ?: [];
    $access = $data->access_identification ?? '';
    $map = theme_map($access);
    $rows = getListByModel($data, $data->home_page_num ?? 6);
    $limit = (int) ($home_config['home_page_num'] ?? 0) ?: 0;
@endphp
<div class="mx-home-section" @if(($home_config['show_home_type'] ?? '') == "color" && !empty($home_config['home_page_bg_color']))
     style='background-color:{{ $home_config['home_page_bg_color'] }} !important'
     @elseif(($home_config['show_home_type'] ?? '') == "img" && !empty($home_config['home_page_bg_img']))
     style='background-image: url({{ GetUrlByPath($home_config['home_page_bg_img']) }});background-repeat: no-repeat'
    @endif>
    <div class="container">
        @themeInclude('public.homeSectionHeader', [
            'sectionData' => $data,
            'sectionConfig' => $home_config,
            'sectionMoreUrl' => model_list_url($access),
        ])

        <div class="mx-product-grid">
            @forelse($rows as $row)
                @continue($limit > 0 && $loop->iteration > $limit)
                @php($it = theme_record($row, $access, $map))
                <article class="mx-product">
                    <a href="{{ $it['link'] }}" class="mx-product__media">
                        @if($it['cover_url'] !== '')
                            <img src="{{ $it['cover_url'] }}" alt="{{ $it['title'] }}" loading="lazy">
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
                        <h3 class="mx-product__title">
                            <a href="{{ $it['link'] }}">{{ $it['title'] !== '' ? $it['title'] : themeTrans('home.product_no', ['id' => $it['id']]) }}</a>
                        </h3>
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
            @empty
                <div class="mx-empty" style="grid-column:1 / -1;">{{ themeTrans('home.empty_product') }}</div>
            @endforelse
        </div>
    </div>
</div>
