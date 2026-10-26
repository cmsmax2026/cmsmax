<div id="content">
    <div class="container">
        @php
            $pageNum = data_get($model, 'home_config.page_num', $model['page_num'] ?? 0);
            $pagePosition = data_get($model, 'home_config.list_page_template', $model['list_page_template'] ?? 'center');
        @endphp

        <div class="mx-list-shell">
            @themeInclude('public.listHero', ['model' => $model, 'listContext' => $listContext ?? []])

            <div class="mx-list-panel">
                <div class="mx-note-list">
                    @forelse($data as $l)
                        <article class="mx-note-card">
                            <h3 class="mx-note-card__title">
                                <a href="{{ model_detail_url($param['model'], $l['id']) }}">{{ $l['title'] ?? themeTrans('common.untitled_content') }}</a>
                            </h3>
                            <p class="mx-note-card__desc">
                                {{ \Illuminate\Support\Str::limit(trim(strip_tags($l['content'] ?? '')), 160) ?: themeTrans('list.no_summary') }}
                            </p>
                            <div class="mx-note-card__meta">
                                @if(!empty($l['created_at']))
                                    <span><i class="fa fa-clock-o"></i> {{$l['created_at']}}</span>
                                @endif
                                @if(!empty($l['author']))
                                    <span><i class="fa fa-user-o"></i> {{$l['author']}}</span>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="mx-empty mx-list-empty">{{ themeTrans('list.empty_data') }}</div>
                    @endforelse
                </div>
            </div>

            @if ($pageNum > 0 && $data_source=="local")
                {{$data->appends($_GET)->links(theme_view('public.pagination'),['data'=>['side_num'=>2,'page_position'=>$pagePosition]])}}
            @endif
        </div>
    </div>
</div>

