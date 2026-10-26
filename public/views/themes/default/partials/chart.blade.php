{{-- {mx:chart /} 的渲染模板：纯 CSS 柱状图，无第三方依赖，同时把数据挂到 data-* 上供 JS 图表库接管
     可用变量：$chart（labels / values / items / group / type）、$attrs（标签属性） --}}
@php
    $values = array_map('floatval', (array) ($chart['values'] ?? []));
    $labels = (array) ($chart['labels'] ?? []);
    $max = $values === [] ? 0 : max(array_map('abs', $values));
    if ($max <= 0) {
        $max = 1;
    }
@endphp
<div class="mx-chart"
     data-chart-type="{{ $chart['type'] ?? 'count' }}"
     data-chart-labels="{{ json_encode($labels, JSON_UNESCAPED_UNICODE) }}"
     data-chart-values="{{ json_encode($values, JSON_UNESCAPED_UNICODE) }}">
    @if($labels === [])
        <div class="mx-empty">{{ themeTrans('chart.empty') }}</div>
    @else
        <div class="mx-chart__bars">
            @foreach($values as $index => $value)
                <div class="mx-chart__bar">
                    <span class="mx-chart__value">{{ rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') }}</span>
                    <span class="mx-chart__column" style="height: {{ max(2, round(abs($value) / $max * 100)) }}%;"></span>
                    <span class="mx-chart__label">{{ $labels[$index] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
