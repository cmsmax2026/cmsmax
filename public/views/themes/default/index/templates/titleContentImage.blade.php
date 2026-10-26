@php $home_config = json_decode($data->home_config,true);  @endphp
<div @if($home_config['show_home_type']=="color" && $home_config['home_page_bg_color'])
         style='background-color:{{$home_config['home_page_bg_color']}} !important'
     @elseif($home_config['show_home_type']=="img" && $home_config['home_page_bg_img'])
         style='background-image: url({{GetUrlByPath($home_config['home_page_bg_img'])}});background-repeat: no-repeat'
    @endif >
    <div class="container">
        @themeInclude('public.homeSectionHeader', [
            'sectionData' => $data,
            'sectionConfig' => $home_config,
            'sectionMoreUrl' => model_list_url($data->access_identification),
        ])

        <div class="mx-home-feature-list">
            @forelse(getListByModel($data,$data->home_page_num) as $d)
                @php($item = toArray($d))
                <a href="{{ model_detail_url($data->access_identification, $item['id']) }}" class="mx-home-feature text-none-decoration">
                    <div class="mx-home-feature__thumb">
                        @if(!empty($item['cover']))
                            <img src="{{ GetUrlByPath($item['cover'])}}" class="image" alt="{{ $item['title'] ?? 'box image' }}">
                        @else
                            <div class="mx-home-card__thumb-placeholder">{{ mb_substr($item['title'] ?? $data->name, 0, 1) }}</div>
                        @endif
                    </div>
                    <div class="mx-home-feature__body">
                        <h3 class="mx-home-feature__title">{{ $item['title'] ?? themeTrans('common.untitled_content') }}</h3>
                        <p class="mx-home-feature__desc">{{ \Illuminate\Support\Str::limit(trim(strip_tags($item['content'] ?? '')), 140) ?: themeTrans('common.click_full_content') }}</p>
                    </div>
                </a>
            @empty
                <div class="mx-empty">{{ themeTrans('home.empty_image_text') }}</div>
            @endforelse
        </div>
    </div>
</div>
