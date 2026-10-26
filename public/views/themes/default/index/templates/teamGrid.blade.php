@php
    $home_config = json_decode($data->home_config, true) ?: [];
    $access = $data->access_identification ?? '';
    $map = theme_map($access);
    $rows = getListByModel($data, $data->home_page_num ?? 4);
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

        <div class="mx-team-grid">
            @forelse($rows as $row)
                @php($it = theme_record($row, $access, $map))
                <article class="mx-team-card">
                    <div class="mx-team-card__avatar">
                        @if($it['cover_url'] !== '')
                            <img src="{{ $it['cover_url'] }}" alt="{{ $it['title'] }}" loading="lazy">
                        @else
                            <span class="mx-team-card__initial">{{ mb_substr($it['title'], 0, 1) ?: themeTrans('home.member_initial') }}</span>
                        @endif
                    </div>
                    <div class="mx-team-card__body">
                        <h3 class="mx-team-card__name">
                            <a href="{{ $it['link'] }}">{{ $it['title'] !== '' ? $it['title'] : themeTrans('home.member_no', ['id' => $it['id']]) }}</a>
                        </h3>
                        @if($it['position'] !== '')
                            <p class="mx-team-card__position">{{ $it['position'] }}</p>
                        @endif
                        @if($it['summary'] !== '')
                            <p class="mx-team-card__desc">{{ \Illuminate\Support\Str::limit($it['summary'], 60) }}</p>
                        @endif
                    </div>
                </article>
            @empty
                <div class="mx-empty" style="grid-column:1 / -1;">{{ themeTrans('home.empty_team') }}</div>
            @endforelse
        </div>
    </div>
</div>
