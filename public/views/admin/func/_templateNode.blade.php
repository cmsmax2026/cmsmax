@if($node['type'] === 'dir')
    <li class="mx-dir">
        <span class="mx-dir-name"><i class="fa fa-folder"></i> {{ $node['name'] }}</span>
        <ul class="list-unstyled ml-3">
            @foreach($node['children'] as $child)
                @include('admin.func._templateNode', ['node' => $child, 'theme' => $theme, 'current' => $current])
            @endforeach
        </ul>
    </li>
@else
    <li class="mx-file">
        <a href="#" data-file="{{ $node['path'] }}"
           class="{{ $node['path'] === $current ? 'font-weight-bold text-primary' : '' }}">
            <i class="fa fa-file-code"></i> {{ $node['name'] }}
        </a>
    </li>
@endif
