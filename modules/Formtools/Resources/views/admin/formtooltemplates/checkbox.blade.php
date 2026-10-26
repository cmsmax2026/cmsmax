@if($f['formtype']=='checkbox')
    @if(!$f['showtype'] || $f['showtype']=='row')
        <div class="form-group row">
            <label class="control-label col-lg-1">
                @if($f['required'])<span style="color:red;">*</span> @endif{{$f['name']}}
            </label>
            <div class="col-lg-11">
                @foreach($f['datas'] as $r)
                    @php
                        $rVal = is_array($r) ? ($r['value'] ?? $r['id'] ?? null) : $r;
                        $rName = is_array($r) ? ($r['name'] ?? $r['label'] ?? $rVal) : $r;
                    @endphp
                    <label class="checkbox-inline">
                        <input type="checkbox"
                               name="{{$f['identification']}}[]"
                               id="{{$f['identification']}}"
                               value="{{$rVal}}"
                               {{$f['disabled']}}
                               @if(in_array((string) $rVal, explode(",", (string) ($f['value'] ?? '')))) checked @endif
                        >{{$rName}}
                    </label>
                @endforeach
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
            <br>
            @foreach($f['datas'] as $r)
                @php
                    $rVal = is_array($r) ? ($r['value'] ?? $r['id'] ?? null) : $r;
                    $rName = is_array($r) ? ($r['name'] ?? $r['label'] ?? $rVal) : $r;
                @endphp
                <label class="checkbox-inline">
                    <input type="checkbox"
                           name="{{$f['identification']}}[]"
                           id="{{$f['identification']}}"
                           value="{{$rVal}}"
                           {{$f['disabled']}}
                           @if(in_array((string) $rVal, explode(",", (string) ($f['value'] ?? '')))) checked @endif
                    >{{$rName}}
                </label>
            @endforeach
            @if($f['notes'])
                <span class="help-block">{{ $f['notes'] }}</span>
            @endif
        </div>
    @endif
@endif
