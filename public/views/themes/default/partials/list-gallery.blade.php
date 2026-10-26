{{-- {mx:list tpl="gallery" /} 的图片瀑布（点击放大）
     可用变量：$rows / $items / $result / $attrs / $map --}}
@php
    $cols = max(1, min(6, (int) ($attrs['cols'] ?? 3)));
    $group = 'mx-partial-gallery-' . substr(md5(json_encode($attrs)), 0, 8);
@endphp
<div class="mx-gallery" style="--mx-gallery-columns: {{ $cols }};" data-mx-lightbox-group="{{ $group }}">
    @foreach($rows as $row)
        @php($it = theme_record($row['raw'] ?? $row, (string) ($result['access'] ?? ''), $map))
        @php($title = $it['title'] !== '' ? $it['title'] : '')
        @foreach($it['gallery'] as $image)
            <a href="{{ $image }}" class="mx-gallery__item" data-mx-lightbox data-caption="{{ $title }}">
                <img src="{{ $image }}" alt="{{ $title }}" loading="lazy">
                <span class="mx-gallery__zoom"><i class="fa fa-search-plus"></i></span>
            </a>
        @endforeach
    @endforeach
</div>
