@php
    $home_config = json_decode($data->home_config, true) ?: [];
    $access = $data->access_identification ?? '';
    $map = theme_map($access);
    $rows = getListByModel($data, $data->home_page_num ?? 5);
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

        <div class="mx-article-list">
            @forelse($rows as $row)
                @php($it = theme_record($row, $access, $map))
                <article class="mx-article-card @if($it['cover_url'] === '') mx-article-card--plain @endif">
                    @if($it['cover_url'] !== '')
                        <a href="{{ $it['link'] }}" class="mx-article-card__thumb">
                            <img src="{{ $it['cover_url'] }}" alt="{{ $it['title'] }}" loading="lazy">
                        </a>
                    @endif
                    <div class="mx-article-card__body">
                        <h3 class="mx-article-card__title">
                            <a href="{{ $it['link'] }}">{{ $it['title'] !== '' ? $it['title'] : themeTrans('common.content_no', ['id' => $it['id']]) }}</a>
                        </h3>
                        @if($it['summary'] !== '')
                            <p class="mx-article-card__desc">{{ \Illuminate\Support\Str::limit($it['summary'], 96) }}</p>
                        @endif
                        <div class="mx-article-card__meta">
                            @if($it['date'] !== '')
                                <span><i class="fa fa-clock-o"></i> {{ $it['date'] }}</span>
                            @endif
                            @if(!empty($it['raw']['author']))
                                <span><i class="fa fa-user-o"></i> {{ $it['raw']['author'] }}</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="mx-empty">{{ themeTrans('home.empty_article') }}</div>
            @endforelse
        </div>
    </div>
</div>
