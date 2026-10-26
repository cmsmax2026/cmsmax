@php
    $wrapperClass = $level === 0 ? 'ft-doc-tree__root' : 'ft-doc-node__children';
@endphp
<ul class="{{ $wrapperClass }}" style="margin: 0; padding: 0; list-style: none;">
    @foreach($nodes as $node)
        @php
            $rowArray = is_object($node) ? get_object_vars($node) : (array) $node;
            $nodeId = (int) ($rowArray['id'] ?? 0);
            $title = (string) ($rowArray[$labelColumn] ?? ($rowArray['title'] ?? ($rowArray['name'] ?? 'ID #' . $nodeId)));
            $price = isset($rowArray['price']) ? floatval($rowArray['price']) : 0.0;
            $status = (string) ($rowArray['status'] ?? '');
            $isActive = $nodeId === $selectedId;
            $hasKids = !empty($rowArray['children']);
            $rowMeta = $statusMeta($status);
            $statusDotClass = preg_replace('/^.*--/', '', $rowMeta['class']);
        @endphp
        <li class="ft-doc-node @if($hasKids) is-expanded @endif @if(!$hasKids) is-leaf @endif" data-node-id="{{ $nodeId }}">
            <div class="ft-doc-node__row @if($isActive) is-active @endif" data-doc-id="{{ $nodeId }}" title="{{ $title }}">
                <span class="ft-doc-node__caret">
                    <i class="icon-arrow-right15"></i>
                </span>
                <span class="ft-doc-node__icon"><i class="icon-file-text2"></i></span>
                <span class="ft-doc-node__title">{{ $title }}</span>
                @if($hasPrice)
                    @if($price > 0)
                        <span class="ft-doc-node__price">￥{{ number_format($price, 2) }}</span>
                    @else
                        <span class="ft-doc-node__price ft-doc-node__price--free">{{ getTranslateByKey('ft_free') }}</span>
                    @endif
                @endif
                @if($hasStatus)
                    <span class="ft-doc-node__status ft-doc-node__status--{{ $statusDotClass }}" title="{{ $rowMeta['label'] }}"></span>
                @endif
            </div>
            @if($hasKids)
                @include('formtools::admin.model._docTreeNode', [
                    'nodes' => $rowArray['children'],
                    'level' => $level + 1,
                    'selectedId' => $selectedId,
                    'labelColumn' => $labelColumn,
                    'hasPrice' => $hasPrice,
                    'hasStatus' => $hasStatus,
                    'statusMeta' => $statusMeta,
                ])
            @endif
        </li>
    @endforeach
</ul>
