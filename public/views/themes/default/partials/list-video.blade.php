{{-- {mx:list tpl="video" /} 的视频卡片网格（点击弹窗播放）
     可用变量：$rows / $items / $result / $attrs / $map --}}
@php($cols = max(1, min(6, (int) ($attrs['cols'] ?? 3))))
<div class="mx-video-grid" style="grid-template-columns: repeat({{ $cols }}, minmax(0, 1fr));">
    @foreach($rows as $row)
        @php($it = theme_record($row['raw'] ?? $row, (string) ($result['access'] ?? ''), $map))
        @php($title = $it['title'] !== '' ? $it['title'] : themeTrans('home.video_no', ['id' => $it['id']]))
        <article class="mx-video-card">
            <button type="button"
                    class="mx-video-card__media"
                    data-mx-video
                    data-video-type="{{ $it['video_type'] }}"
                    data-video-src="{{ $it['video_url'] }}"
                    data-video-title="{{ $title }}"
                    @if($it['video_url'] === '') disabled @endif>
                @if($it['cover_url'] !== '')
                    <img src="{{ $it['cover_url'] }}" alt="{{ $title }}" loading="lazy">
                @else
                    <span class="mx-product__placeholder"><i class="fa fa-film"></i></span>
                @endif
                <span class="mx-video-play"><i class="fa fa-play"></i></span>
            </button>
            <div class="mx-video-card__body">
                <h3 class="mx-video-card__title">
                    @if($it['link'] !== '')
                        <a href="{{ $it['link'] }}">{{ $title }}</a>
                    @else
                        {{ $title }}
                    @endif
                </h3>
                @if($it['summary'] !== '')
                    <p class="mx-video-card__desc">{{ \Illuminate\Support\Str::limit($it['summary'], 60) }}</p>
                @endif
            </div>
        </article>
    @endforeach
</div>
