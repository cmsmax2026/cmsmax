{{-- 问答折叠模板：problem/answer 成对字段，点击展开，适合 FAQ、帮助中心、常见问题 --}}
@php
    $access = $param['model'] ?? '';
    $map = theme_map($access);
    $pageNum = (int) data_get($model, 'home_config.page_num', $model['page_num'] ?? 0);
    $pagePosition = data_get($model, 'home_config.list_page_template', $model['list_page_template'] ?? 'center');
@endphp
<div id="content">
    <div class="container">
        <div class="mx-list-shell">
            @themeInclude('public.listHero', ['model' => $model, 'listContext' => $listContext ?? []])

            <div class="mx-list-panel">
                <div class="mx-faq" data-mx-accordion>
                    @forelse($data as $row)
                        @php($it = theme_record($row, $access, $map))
                        <article class="mx-faq__item @if($loop->first) is-open @endif">
                            <button type="button" class="mx-faq__question" data-mx-accordion-trigger>
                                <span>{{ $it['title'] !== '' ? $it['title'] : ('问题 #' . $it['id']) }}</span>
                                <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="mx-faq__answer" @if(!$loop->first) hidden @endif>
                                <div class="mx-faq__answer-inner">
                                    @if($it['content'] !== '')
                                        {!! $it['content'] !!}
                                    @else
                                        {{ $it['summary'] !== '' ? $it['summary'] : '该问题暂未填写答案。' }}
                                    @endif
                                    @if($it['link'] !== '')
                                        <a class="mx-faq__more" href="{{ $it['link'] }}">查看完整说明 <i class="fa fa-angle-right"></i></a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="mx-empty mx-list-empty">当前还没有问答内容，请先在后台新增数据。</div>
                    @endforelse
                </div>
            </div>

            @if ($pageNum > 0 && ($data_source ?? 'local') === 'local' && is_object($data) && method_exists($data, 'links'))
                {{ $data->appends(request()->except('page'))->links(theme_view('public.pagination'), ['data' => ['side_num' => 2, 'page_position' => $pagePosition]]) }}
            @endif
        </div>
    </div>
</div>
