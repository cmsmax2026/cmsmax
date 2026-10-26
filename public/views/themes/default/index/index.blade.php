@themeInclude('public.head')
@themeInclude('public.preloader')

<div class="page js-page ">
    @themeInclude('public.topMenu', ['model'=>['home_page_title'=>true]])

    @themeInclude('public.themeBanner', ['identification' => 'homepage', 'displayMode' => 'single'])
    @themeInclude('public.themeAdvert', ['identification' => 'homepage_notice', 'displayMode' => 'single', 'columns' => 1])

    <div class="mx-home-sections">
        @foreach($models as $index=>$model)
            @php($homeConfig = json_decode($model->home_config, true) ?: [])
            @if($homeConfig)
                @php($visualHomeSection = form_model_home_section_html($model))
                @php($homeSectionView = form_model_home_section_view($model, ($homeConfig['list_template'] ?? 'list')))
                <section class="mx-home-section">
                    @if($visualHomeSection)
                        {!! $visualHomeSection !!}
                    @elseif($homeSectionView)
                        @includeIf($homeSectionView, [
                            "data" => $model
                        ])
                    @endif
                </section>
            @endif
        @endforeach
    </div>
    @themeInclude('public.themeBanner', ['identification' => 'homepage_grid', 'displayMode' => 'multiple', 'limit' => 3])
    @themeInclude('public.themeAdvert', ['identification' => 'homepage_cards', 'displayMode' => 'multiple', 'columns' => 3, 'limit' => 3])
    <footer>
            @themeInclude('public.bottomMenu')
        <div class="footer">
            @themeInclude('public.footerMenu')
        </div>
    </footer>
</div>
@themeInclude('public.js')
</body>
</html>
