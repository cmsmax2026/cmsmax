@php
    $wrapperClass = $wrapperClass ?? 'ft-doc-node__children';
    $activeContentId = (int) ($selectedId ?? 0);
    $activeCategoryId = (int) ($activeCategoryId ?? 0);
@endphp
<ul class="{{ $wrapperClass }}">
    @foreach($nodes ?? [] as $node)
        @php
            $type = (string) ($node['type'] ?? 'category');
            $nodeId = (int) ($node['id'] ?? 0);
            $title = (string) ($node['title'] ?? '');
            $price = (float) ($node['price'] ?? 0);
            $status = (string) ($node['status'] ?? '1');
            $url = (string) ($node['url'] ?? '');
            $clickable = (bool) ($node['clickable'] ?? ($url !== ''));
            $childCategories = $node['children'] ?? [];
            $contentItems = $node['contents'] ?? [];
            $isActiveContent = $type === 'content' && $nodeId === $activeContentId;
            $isActiveCategory = $type === 'category' && $nodeId === $activeCategoryId;
            $isActive = $isActiveContent || $isActiveCategory;
            $hasKids = !empty($childCategories) || !empty($contentItems);
            if ($url === '' && $clickable && $access) {
                $url = model_detail_url($access, $nodeId);
            }
            $icon = $type === 'content' ? 'icon-file-text2' : 'icon-folder';
        @endphp
        <li class="ft-doc-node ft-doc-node--{{ $type }} @if($hasKids) is-expanded @endif @if(!$hasKids) is-leaf @endif">
            @if($clickable && $url !== '')
                <a href="{{ $url }}" class="ft-doc-node__row @if($isActive) is-active @endif" title="{{ $title }}">
                    <span class="ft-doc-node__icon"><i class="{{ $icon }}"></i></span>
                    <span class="ft-doc-node__title">{{ $title }}</span>
                    @if($price > 0)
                        <span class="ft-doc-node__price">￥{{ number_format($price, 2) }}</span>
                    @else
                        <span class="ft-doc-node__price ft-doc-node__price--free">{{ $type === 'category' ? '分类' : '免费' }}</span>
                    @endif
                </a>
            @else
                <div class="ft-doc-node__row ft-doc-node__row--static @if($isActive) is-active @endif" title="{{ $title }}">
                    <span class="ft-doc-node__icon"><i class="{{ $icon }}"></i></span>
                    <span class="ft-doc-node__title">{{ $title }}</span>
                    @if($price > 0)
                        <span class="ft-doc-node__price">￥{{ number_format($price, 2) }}</span>
                    @else
                        <span class="ft-doc-node__price ft-doc-node__price--free">{{ $type === 'category' ? '分类' : '免费' }}</span>
                    @endif
                </div>
            @endif
            @if(!empty($childCategories))
                @include('formtools::home._docTreeNode', [
                    'nodes' => $childCategories,
                    'level' => ($level ?? 0) + 1,
                    'selectedId' => $selectedId,
                    'activeCategoryId' => $activeCategoryId,
                    'access' => $access,
                    'wrapperClass' => 'ft-doc-node__children',
                ])
            @endif
            @if(!empty($contentItems))
                <ul class="ft-doc-tree__contents">
                    @foreach($contentItems as $content)
                        @php
                            $cId = (int) ($content['id'] ?? 0);
                            $cTitle = (string) ($content['title'] ?? '');
                            $cUrl = (string) ($content['url'] ?? ($access ? model_detail_url($access, $cId) : '#'));
                            $cPrice = (float) ($content['price'] ?? 0);
                            $cActive = $cId === $activeContentId;
                        @endphp
                        <li class="ft-doc-node ft-doc-node--content @if($cActive) is-current @endif">
                            <a href="{{ $cUrl }}" class="ft-doc-node__row @if($cActive) is-active @endif" title="{{ $cTitle }}">
                                <span class="ft-doc-node__icon"><i class="icon-file-text2"></i></span>
                                <span class="ft-doc-node__title">{{ $cTitle }}</span>
                                @if($cPrice > 0)
                                    <span class="ft-doc-node__price">￥{{ number_format($cPrice, 2) }}</span>
                                @else
                                    <span class="ft-doc-node__price ft-doc-node__price--free">免费</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </li>
    @endforeach
</ul>
