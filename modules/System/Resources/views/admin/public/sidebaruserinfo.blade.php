<div class="sidebar-user">
    <div class="category-content">
        <div class="media">
            <a href="#" class="media-left">
                @if($userInfo['avatar'])
                <img class="img-circle img-sm" alt="" src="{{GetUrlByPath($userInfo['avatar'])}}">
                @else
                @php
                    $lxmInitial = mb_substr(($userInfo['username'] ?? '?'), 0, 1);
                    $lxmPalette = ['#4f46e5','#0ea5e9','#10b981','#f59e0b','#ec4899','#8b5cf6','#ef4444','#14b8a6'];
                    $lxmColor = $lxmPalette[abs(crc32($userInfo['username'] ?? 'x')) % count($lxmPalette)];
                @endphp
                <span class="img-circle img-sm" style="display:inline-flex;align-items:center;justify-content:center;background:{{$lxmColor}};color:#fff;font-weight:600;font-size:15px;">{{$lxmInitial}}</span>
                @endif
            </a>
            <div class="media-body">
                        <span class="media-heading text-semibold">
                            {{$userInfo['username']}} <br />
                            @if($userInfo['type']=='admin')
                                超级管理员
                            @else
                                用户
                            @endif
                        </span>

            </div>

        </div>
    </div>
</div>
