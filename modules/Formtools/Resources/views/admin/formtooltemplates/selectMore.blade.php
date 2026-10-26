@if($f['formtype']=='selectMore')

    @if(!$f['showtype'] || $f['showtype']=='row')
        <div class="form-group row">
            <label class="control-label col-lg-1">
                @if($f['required'])<span style="color:red;">*</span> @endif{{$f['name']}}
            </label>
            <div class="col-lg-11">
                <select class="select-search" multiple {{$f['disabled']}}
                name="{{$f['identification']}}" id="{{$f['identification']}}"
                        @if($f['required']) required @endif>
                    @php
                        $selectedValues = $f['value'] ?? [];
                        if (!is_array($selectedValues)) {
                            $selectedValues = json_decode((string) $selectedValues, true) ?: [];
                        }
                        $renderOptions = function ($nodes, $depth = 0) use (&$renderOptions, $selectedValues) {
                            $html = '';
                            foreach ((array) $nodes as $node) {
                                if (!is_array($node)) continue;
                                $value = $node['value'] ?? '';
                                $name = (string) ($node['name'] ?? '');
                                $prefix = $depth > 0 ? str_repeat('　', $depth) . 'ㄴ ' : '';
                                $selected = in_array($value, $selectedValues) ? ' selected' : '';
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
                @if($f['notes'])
                    <span class="help-block">{{ $f['notes'] }}</span>
                @endif
            </div>
        </div>
    @elseif($f['showtype']=='column')
        <div class="col-md-6" style="margin-bottom: 15px;">
            <label>
                @if($f['required'])<span style="color:red;">*</span> @endif{{$f['name']}}
            </label>
            <select class="select-search" {{$f['disabled']}}
            name="{{$f['identification']}}" id="{{$f['identification']}}"
                    @if($f['required']) required @endif>
                <option value="">{{ getTranslateByKey('common_select') }}</option>
                @php
                    $selectedValue = (string) ($f['value'] ?? '');
                    $renderOptions = function ($nodes, $depth = 0) use (&$renderOptions, $selectedValue) {
                        $html = '';
                        foreach ((array) $nodes as $node) {
                            if (!is_array($node)) continue;
                            $value = $node['value'] ?? '';
                            $name = (string) ($node['name'] ?? '');
                            $prefix = $depth > 0 ? str_repeat('　', $depth) . 'ㄴ ' : '';
                            $selected = ((string) $value === $selectedValue) ? ' selected' : '';
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
            @if($f['notes'])
                <span class="help-block">{{ $f['notes'] }}</span>
            @endif
        </div>
    @endif
@endif
