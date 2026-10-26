@php
    $listContext = $listContext ?? [];
    $pageTitle = $listContext['pageTitle'] ?? ($model['home_config']['home_page_title'] ?? ($model['name'] ?? themeTrans('list.default_title')));
    $pageDescription = $listContext['pageDescription'] ?? ($model['home_config']['home_page_describe'] ?? themeTrans('list.default_description'));
    $searchKeyword = $listContext['keyword'] ?? '';
    $hasKeyword = (bool) ($listContext['hasKeyword'] ?? false);
    $searchFieldNames = $listContext['searchFieldNames'] ?? [];
    $searchFieldSummary = $listContext['searchFieldSummary'] ?? '';
    $resultCount = (int) ($listContext['resultCount'] ?? 0);
    $totalCount = (int) ($listContext['totalCount'] ?? $resultCount);
    $hasPid = (bool) ($listContext['hasPid'] ?? false);
    $pid = (string) ($listContext['pid'] ?? '');
    $pidScope = (string) ($listContext['pidScope'] ?? 'descendants');
    $pidOptions = $listContext['pidOptions'] ?? [];
    $sort = (string) ($listContext['sort'] ?? 'newest');
    $sortOptions = $listContext['sortOptions'] ?? [];
    $pidScopeOptions = $listContext['pidScopeOptions'] ?? [];
    $currentUrl = url()->current();
    $queryWithoutKeyword = request()->except(['keyword', 'page', 'pid', 'pid_scope', 'sort']);
@endphp

<div class="mx-list-hero">
    <div class="mx-list-hero__main">
        <div class="mx-list-hero__eyebrow">Content Explorer</div>
        <h1 class="mx-list-hero__title">{{ $pageTitle }}</h1>
        <p class="mx-list-hero__description">{{ $pageDescription }}</p>
        <div class="mx-list-hero__stats">
            <span class="mx-list-stat">
                <strong>{{ $resultCount }}</strong>
                <em>{{ $hasKeyword ? themeTrans('list.current_results') : themeTrans('list.current_content') }}</em>
            </span>
            <span class="mx-list-stat">
                <strong>{{ $totalCount }}</strong>
                <em>{{ $hasKeyword ? themeTrans('list.total_matches') : themeTrans('list.total_content') }}</em>
            </span>
            @if($searchFieldNames)
                <span class="mx-list-stat">
                    <strong>{{ count($searchFieldNames) }}</strong>
                    <em>{{ themeTrans('list.search_fields') }}</em>
                </span>
            @endif
        </div>
    </div>

    <div class="mx-search-panel">
        <form method="get" action="{{ $currentUrl }}" class="mx-search-form">
            @foreach($queryWithoutKeyword as $queryKey => $queryValue)
                @if(is_array($queryValue))
                    @foreach($queryValue as $subValue)
                        <input type="hidden" name="{{ $queryKey }}[]" value="{{ $subValue }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                @endif
            @endforeach

            @if($hasPid)
                <label class="mx-search-form__label" for="mx-list-search-pid">{{ themeTrans('list.category_filter') }}</label>
                <div class="mx-search-form__row">
                    <div class="mx-search-form__field" style="flex: 1; min-width: 220px;">
                        <i class="fa fa-sitemap"></i>
                        <select id="mx-list-search-pid" name="pid" class="form-control" style="height: 44px;">
                            <option value="">{{ themeTrans('list.all_categories') }}</option>
                            @foreach($pidOptions as $opt)
                                @php($val = (string) ($opt['value'] ?? ''))
                                <option value="{{$val}}" @if($pid!=='' && $pid===$val) selected @endif>{{ $opt['label'] ?? $opt['name'] ?? $val }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="button">{{ themeTrans('list.filter') }}</button>
                    <a href="{{ $currentUrl }}" class="mx-search-form__reset">{{ themeTrans('list.reset') }}</a>
                </div>
            @else
                <label class="mx-search-form__label" for="mx-list-search-keyword">{{ themeTrans('list.site_search') }}</label>
                <div class="mx-search-form__row">
                    <div class="mx-search-form__field">
                        <i class="fa fa-search"></i>
                        <input
                            id="mx-list-search-keyword"
                            type="text"
                            name="keyword"
                            value="{{ $searchKeyword }}"
                            class="form-control"
                            placeholder="{{ $searchFieldSummary ? themeTrans('list.searchable', ['fields' => $searchFieldSummary]) : themeTrans('list.keyword_placeholder') }}"
                        >
                    </div>
                    <button type="submit" class="button">{{ themeTrans('list.search_button') }}</button>
                    @if($hasKeyword)
                        <a href="{{ $currentUrl . ($queryWithoutKeyword ? ('?' . http_build_query($queryWithoutKeyword)) : '') }}" class="mx-search-form__reset">{{ themeTrans('list.clear') }}</a>
                    @endif
                </div>
            @endif
        </form>

        @if(!$hasPid)
            <div class="mx-search-panel__meta">
                @if($searchFieldNames)
                    <span class="mx-search-chip">{{ themeTrans('list.fields', ['fields' => $searchFieldSummary]) }}</span>
                @endif
                @if($hasKeyword)
                    <span class="mx-search-chip mx-search-chip--active">{{ themeTrans('list.keyword', ['keyword' => $searchKeyword]) }}</span>
                @else
                    <span class="mx-search-chip">{{ themeTrans('list.keyword_hint') }}</span>
                @endif
            </div>
        @endif
    </div>
</div>

@themeInclude('public.themeBanner', ['identification' => 'list_top', 'displayMode' => 'single', 'class' => 'header-back header-back-default'])
@themeInclude('public.themeAdvert', ['identification' => 'list_inline', 'displayMode' => 'multiple', 'columns' => 2, 'limit' => 2])
