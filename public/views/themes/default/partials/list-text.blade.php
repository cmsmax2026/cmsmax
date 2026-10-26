{{-- {mx:list tpl="text" /} 的纯标题列表
     可用变量：$rows / $items / $result / $attrs / $map --}}
<ul class="mx-shorttag-list">
    @foreach($rows as $row)
        @php($title = $row['title'] !== '' ? $row['title'] : ('#' . $row['id']))
        <li>
            @if(!empty($row['link']))
                <a href="{{ $row['link'] }}">{{ $title }}</a>
            @else
                {{ $title }}
            @endif
            @if(!empty($row['date']))
                <span class="mx-shorttag-list__date">{{ $row['date'] }}</span>
            @endif
        </li>
    @endforeach
</ul>
