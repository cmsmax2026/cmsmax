@php
    $home_config = json_decode($data->home_config, true) ?: [];
    $access = $data->access_identification ?? '';
    $map = theme_map($access);
    $rows = getListByModel($data, $data->home_page_num ?? 3);
    $recommendKeys = ['recommended', 'recommend', 'is_recommend', 'hot', 'featured'];
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

        <div class="mx-price-grid">
            @forelse($rows as $row)
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
                        <span class="mx-price-card__ribbon">{{ themeTrans('common.recommended') }}</span>
                    @endif
                    <h3 class="mx-price-card__name">{{ $it['title'] !== '' ? $it['title'] : themeTrans('home.package_no', ['id' => $it['id']]) }}</h3>
                    @if($it['spec'] !== '')
                        <p class="mx-price-card__spec">{{ $it['spec'] }}</p>
                    @endif
                    <div class="mx-price-card__price">
                        @if($it['price'] !== '')
                            <strong>{{ $it['price'] }}</strong>
                        @else
                            <strong class="mx-price--quote">{{ themeTrans('common.price_quote') }}</strong>
                        @endif
                    </div>
                    @if($it['summary'] !== '')
                        <ul class="mx-price-card__list">
                            @foreach((array) preg_split('/[\r\n]+/', trim($it['summary'])) as $line)
                                @continue(trim($line) === '')
                                <li><i class="fa fa-check"></i> {{ trim($line, " \t\n\r\0\x0B-·•") }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <a class="mx-price-card__action" href="{{ $it['link'] ?: 'javascript:void(0)' }}">{{ themeTrans('common.select_now') }}</a>
                </article>
            @empty
                <div class="mx-empty" style="grid-column:1 / -1;">{{ themeTrans('home.empty_price') }}</div>
            @endforelse
        </div>
    </div>
</div>
