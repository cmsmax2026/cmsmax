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

        <div class="mx-faq" data-mx-accordion>
            @forelse($rows as $row)
                @php($it = theme_record($row, $access, $map))
                <article class="mx-faq__item @if($loop->first) is-open @endif">
                    <button type="button" class="mx-faq__question" data-mx-accordion-trigger>
                        <span>{{ $it['title'] !== '' ? $it['title'] : themeTrans('home.faq_no', ['id' => $it['id']]) }}</span>
                        <i class="fa fa-angle-down"></i>
                    </button>
                    <div class="mx-faq__answer" @if(!$loop->first) hidden @endif>
                        <div class="mx-faq__answer-inner">
                            @if($it['content'] !== '')
                                {!! $it['content'] !!}
                            @else
                                {{ $it['summary'] !== '' ? $it['summary'] : themeTrans('home.faq_no_answer') }}
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="mx-empty">{{ themeTrans('home.empty_faq') }}</div>
            @endforelse
        </div>
    </div>
</div>
