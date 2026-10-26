@php
    $home_config = json_decode($data->home_config, true) ?: [];
    $access = $data->access_identification ?? '';
    $map = theme_map($access);
    $rows = getListByModel($data, $data->home_page_num ?? 9);
    $columns = (int) theme_setting('gallery_columns', 3);
    $columns = $columns >= 2 && $columns <= 5 ? $columns : 3;
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

        <div class="mx-gallery mx-gallery--home" style="--mx-gallery-columns: {{ $columns }};" data-mx-lightbox-group="home-gallery-{{ $access }}">
            @forelse($rows as $row)
                @php($it = theme_record($row, $access, $map))
                @php($images = $it['gallery'])
                @if($images === [])
                    @continue
                @endif
                <a href="{{ $images[0] }}"
                   class="mx-gallery__item"
                   data-mx-lightbox
                   data-caption="{{ $it['title'] }}">
                    <img src="{{ $images[0] }}" alt="{{ $it['title'] }}" loading="lazy">
                    @if(count($images) > 1)
                        <span class="mx-gallery__count"><i class="fa fa-th"></i> {{ count($images) }}</span>
                    @endif
                </a>
                @foreach(array_slice($images, 1) as $image)
                    <a href="{{ $image }}" class="is-hidden" data-mx-lightbox data-caption="{{ $it['title'] }}"></a>
                @endforeach
            @empty
                <div class="mx-empty">{{ themeTrans('home.empty_gallery') }}</div>
            @endforelse
        </div>
    </div>
</div>
