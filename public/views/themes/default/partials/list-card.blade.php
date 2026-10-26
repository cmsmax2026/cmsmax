{{-- {mx:list tpl="card" /} 的行渲染模板
     可用变量：$rows（规范化后的行）、$items（原始行）、$result（完整返回）、$attrs（标签属性）、$map（字段别名映射） --}}
@php($cols = max(1, min(6, (int) ($attrs['cols'] ?? 3))))
<div class="mx-card-grid" style="grid-template-columns: repeat({{ $cols }}, minmax(0, 1fr));">
    @foreach($rows as $row)
        @php($title = $row['title'] !== '' ? $row['title'] : ('#' . $row['id']))
        <article class="mx-card">
            @if(!empty($row['cover_url']))
                <a href="{{ $row['link'] }}" class="mx-card__thumb">
                    <img src="{{ $row['cover_url'] }}" alt="{{ $title }}" loading="lazy">
                </a>
            @endif
            <h3 class="mx-card__title"><a href="{{ $row['link'] }}">{{ $title }}</a></h3>
            @if(!empty($row['summary']))
                <p class="mx-card__desc">{{ \Illuminate\Support\Str::limit($row['summary'], 90) }}</p>
            @endif
            @if(!empty($row['date']))
                <div class="mx-card__meta"><span><i class="fa fa-clock-o"></i> {{ $row['date'] }}</span></div>
            @endif
        </article>
    @endforeach
</div>
