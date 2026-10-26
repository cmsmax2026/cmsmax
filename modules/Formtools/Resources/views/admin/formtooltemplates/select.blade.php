@if($f['formtype']=='select')
    @include('formtools::admin.formtooltemplates.fieldWrapperStart', compact('f'))
    <select class="select-search" {{$f['disabled']}}
            name="{{$f['identification']}}" id="{{$f['identification']}}"
            @if($f['required']) required @endif>
        <option value="">{{ getTranslateByKey('common_select') }}</option>
        @php
            $renderOptions = function ($nodes, $depth = 0) use (&$renderOptions, $f) {
                $html = '';
                foreach ((array) $nodes as $node) {
                    if (!is_array($node)) continue;
                    $value = $node['value'] ?? '';
                    $name = (string) ($node['name'] ?? '');
                    $prefix = $depth > 0 ? str_repeat('　', $depth) . 'ㄴ ' : '';
                    $selected = ((string) $value === (string) ($f['value'] ?? '')) ? ' selected' : '';
                    $html .= '<option value="' . e((string) $value) . '"' . $selected . '>' . $prefix . $name . '</option>';
                    $children = $node['children'] ?? ($node['child'] ?? []);
                    if (is_array($children) && $children) {
                        $html .= $renderOptions($children, $depth + 1);
                    }
                }
                return $html;
            };
        @endphp
        {!! $renderOptions($f['datas'] ?? []) !!}
    </select>
    @include('formtools::admin.formtooltemplates.fieldWrapperEnd', compact('f'))
@endif
