{{-- 下载列表模板：文件图标 + 说明 + 下载按钮，适合资料、手册、驱动、软件包 --}}
@php
    $access = $param['model'] ?? '';
    $map = theme_map($access);
    $pageNum = (int) data_get($model, 'home_config.page_num', $model['page_num'] ?? 0);
    $pagePosition = data_get($model, 'home_config.list_page_template', $model['list_page_template'] ?? 'center');
    $iconMap = [
        'pdf' => 'fa-file-pdf-o', 'doc' => 'fa-file-word-o', 'docx' => 'fa-file-word-o',
        'xls' => 'fa-file-excel-o', 'xlsx' => 'fa-file-excel-o', 'ppt' => 'fa-file-powerpoint-o',
        'pptx' => 'fa-file-powerpoint-o', 'zip' => 'fa-file-archive-o', 'rar' => 'fa-file-archive-o',
        '7z' => 'fa-file-archive-o', 'txt' => 'fa-file-text-o', 'mp3' => 'fa-file-audio-o',
        'mp4' => 'fa-file-video-o', 'apk' => 'fa-android',
    ];
@endphp
<div id="content">
    <div class="container">
        <div class="mx-list-shell">
            @themeInclude('public.listHero', ['model' => $model, 'listContext' => $listContext ?? []])

            <div class="mx-list-panel">
                <div class="mx-download-list">
                    @forelse($data as $row)
                        @php($it = theme_record($row, $access, $map))
                        @php($ext = strtolower(pathinfo($it['file_name'], PATHINFO_EXTENSION)))
                        <article class="mx-download">
                            <span class="mx-download__icon"><i class="fa {{ $iconMap[$ext] ?? 'fa-file-o' }}"></i></span>
                            <div class="mx-download__body">
                                <h3 class="mx-download__title">
                                    @if($it['link'] !== '')
                                        <a href="{{ $it['link'] }}">{{ $it['title'] !== '' ? $it['title'] : ('文件 #' . $it['id']) }}</a>
                                    @else
                                        {{ $it['title'] !== '' ? $it['title'] : ('文件 #' . $it['id']) }}
                                    @endif
                                </h3>
                                @if($it['summary'] !== '')
                                    <p class="mx-download__desc">{{ \Illuminate\Support\Str::limit($it['summary'], 90) }}</p>
                                @endif
                                <div class="mx-download__meta">
                                    @if($ext !== '')
                                        <span class="mx-download__ext">{{ strtoupper($ext) }}</span>
                                    @endif
                                    @if($it['date'] !== '')
                                        <span><i class="fa fa-clock-o"></i> {{ $it['date'] }}</span>
                                    @endif
                                    @if(!empty($it['raw']['download_count']))
                                        <span><i class="fa fa-download"></i> {{ (int) $it['raw']['download_count'] }} 次</span>
                                    @endif
                                </div>
                            </div>
                            @if($it['file_url'] !== '')
                                <a class="mx-download__action" href="{{ $it['file_url'] }}" target="_blank" rel="noopener" download>
                                    <i class="fa fa-download"></i>
                                    <span>下载</span>
                                </a>
                            @elseif($it['link'] !== '')
                                <a class="mx-download__action mx-download__action--ghost" href="{{ $it['link'] }}">查看详情</a>
                            @endif
                        </article>
                    @empty
                        <div class="mx-empty mx-list-empty">当前还没有可下载的资料，请先在后台新增内容。</div>
                    @endforelse
                </div>
            </div>

            @if ($pageNum > 0 && ($data_source ?? 'local') === 'local' && is_object($data) && method_exists($data, 'links'))
                {{ $data->appends(request()->except('page'))->links(theme_view('public.pagination'), ['data' => ['side_num' => 2, 'page_position' => $pagePosition]]) }}
            @endif
        </div>
    </div>
</div>
