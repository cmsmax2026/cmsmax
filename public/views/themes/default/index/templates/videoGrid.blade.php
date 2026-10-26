@php
    $home_config = json_decode($data->home_config, true) ?: [];
    $access = $data->access_identification ?? '';
    $map = theme_map($access);
    $rows = getListByModel($data, $data->home_page_num ?? 6);
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

        <div class="mx-video-grid">
            @forelse($rows as $row)
                @php($it = theme_record($row, $access, $map))
                <article class="mx-video-card">
                    <button type="button"
                            class="mx-video-card__media"
                            data-mx-video
                            data-video-type="{{ $it['video_type'] }}"
                            data-video-src="{{ $it['video_url'] }}"
                            data-video-title="{{ $it['title'] }}"
                            @if($it['video_url'] === '') disabled @endif>
                        @if($it['cover_url'] !== '')
                            <img src="{{ $it['cover_url'] }}" alt="{{ $it['title'] }}" loading="lazy">
                        @else
                            <span class="mx-product__placeholder"><i class="fa fa-film"></i></span>
                        @endif
                        <span class="mx-video-play"><i class="fa fa-play"></i></span>
                    </button>
                    <div class="mx-video-card__body">
                        <h3 class="mx-video-card__title">
                            <a href="{{ $it['link'] }}">{{ $it['title'] !== '' ? $it['title'] : themeTrans('home.video_no', ['id' => $it['id']]) }}</a>
                        </h3>
                    </div>
                </article>
            @empty
                <div class="mx-empty" style="grid-column:1 / -1;">{{ themeTrans('home.empty_video') }}</div>
            @endforelse
        </div>
    </div>
</div>
