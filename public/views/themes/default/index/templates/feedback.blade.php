@php $home_config = json_decode($data->home_config,true); @endphp
<div @if($home_config['show_home_type']=="color" && $home_config['home_page_bg_color'])
         style='background-color:{{$home_config['home_page_bg_color']}} !important'
     @elseif($home_config['show_home_type']=="img" && $home_config['home_page_bg_img'])
         style='background-image: url({{GetUrlByPath($home_config['home_page_bg_img'])}});background-repeat: no-repeat'
     @endif>
    <div class="container">
        @themeInclude('public.homeSectionHeader', [
            'sectionData' => $data,
            'sectionConfig' => $home_config,
            'sectionMoreUrl' => model_list_url($data->access_identification),
            'sectionMoreText' => themeTrans('home.leave_message'),
        ])

        <div class="mx-home-grid">
            @forelse(getListByModel($data, $data->home_page_num) as $d)
                <article class="mx-home-card mx-home-card--feedback">
                    <div class="mx-home-card__body">
                        <div class="mx-home-feedback-meta">
                            <strong>{{ $d->full_name ?: themeTrans('home.anonymous_visitor') }}</strong>
                            <span>{{ $d->company ?: themeTrans('home.visitor_message') }}</span>
                        </div>
                        <h3 class="mx-home-card__title">{{ $d->title ?? themeTrans('home.new_message') }}</h3>
                        <p class="mx-home-card__desc">{{ \Illuminate\Support\Str::limit(trim((string) ($d->content ?? '')), 120) }}</p>
                        <div class="mx-home-card__meta">
                            @if(!empty($d->created_at))
                                <span><i class="fa fa-clock-o"></i> {{ $d->created_at }}</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="mx-empty" style="grid-column:1 / -1;">{{ themeTrans('home.empty_feedback') }}</div>
            @endforelse
        </div>
    </div>
</div>
