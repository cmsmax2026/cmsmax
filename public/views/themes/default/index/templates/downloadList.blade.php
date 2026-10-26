@php
    $home_config = json_decode($data->home_config, true) ?: [];
    $access = $data->access_identification ?? '';
    $map = theme_map($access);
    $rows = getListByModel($data, $data->home_page_num ?? 5);
    $iconMap = [
        'pdf' => 'fa-file-pdf-o', 'doc' => 'fa-file-word-o', 'docx' => 'fa-file-word-o',
        'xls' => 'fa-file-excel-o', 'xlsx' => 'fa-file-excel-o', 'ppt' => 'fa-file-powerpoint-o',
        'pptx' => 'fa-file-powerpoint-o', 'zip' => 'fa-file-archive-o', 'rar' => 'fa-file-archive-o',
        '7z' => 'fa-file-archive-o', 'txt' => 'fa-file-text-o', 'mp3' => 'fa-file-audio-o',
        'mp4' => 'fa-file-video-o', 'apk' => 'fa-android',
    ];
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

        <div class="mx-download-list">
            @forelse($rows as $row)
                @php($it = theme_record($row, $access, $map))
                @php($ext = strtolower(pathinfo($it['file_name'], PATHINFO_EXTENSION)))
                <article class="mx-download">
                    <span class="mx-download__icon"><i class="fa {{ $iconMap[$ext] ?? 'fa-file-o' }}"></i></span>
                    <div class="mx-download__body">
                        <h3 class="mx-download__title">
                            <a href="{{ $it['link'] }}">{{ $it['title'] !== '' ? $it['title'] : themeTrans('home.download_no', ['id' => $it['id']]) }}</a>
                        </h3>
                        @if($it['summary'] !== '')
                            <p class="mx-download__desc">{{ \Illuminate\Support\Str::limit($it['summary'], 80) }}</p>
                        @endif
                    </div>
                    @if($it['file_url'] !== '')
                        <a class="mx-download__action" href="{{ $it['file_url'] }}" target="_blank" rel="noopener" download>
                            <i class="fa fa-download"></i><span>{{ themeTrans('common.download') }}</span>
                        </a>
                    @endif
                </article>
            @empty
                <div class="mx-empty">{{ themeTrans('home.empty_download') }}</div>
            @endforelse
        </div>
    </div>
</div>
