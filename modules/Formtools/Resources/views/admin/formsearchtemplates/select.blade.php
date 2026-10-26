@if($f['formtype']=='select')

    <select class="form-control" {{$f['disabled']}} name="{{$f['identification']}}" id="{{$f['identification']}}">
        <option @if($f['value']==="") selected @endif value="">{{ getTranslateByKey('common_select') }}</option>
        @foreach($f['datas'] as $s)
            <option value="{{$s['value']}}"
                    @if($f['value'] !="" && $s['value']==$f['value']) selected @endif
            >{!! $s['name'] !!}</option>
        @endforeach
    </select>

@endif
