{{-- 商品详情模板：左侧图集 + 右侧价格/规格/咨询，正文在下，适合商品、课程、套餐详情 --}}
@php
    $access = $param['model'] ?? '';
    $map = theme_map($access);
    $record = theme_record($detailRecord ?? $data ?? [], $access, $map);
    $gallery = $record['gallery'];
    $detailFields = $model['frontend_schema']['detail'] ?? [];
@endphp

<div id="content">
    <div class="container">
        <div class="mx-detail-shell">
            <div class="mx-product-detail">
                <div class="mx-product-detail__media" data-mx-gallery>
                    @if($gallery !== [])
                        <a href="{{ $gallery[0] }}"
                           class="mx-product-detail__stage"
                           data-mx-lightbox
                           data-mx-lightbox-group="product-{{ $access }}-{{ $record['id'] }}"
                           data-caption="{{ $record['title'] }}">
                            <img src="{{ $gallery[0] }}" alt="{{ $record['title'] }}" data-mx-gallery-main>
                        </a>
                        @if(count($gallery) > 1)
                            <div class="mx-product-detail__thumbs">
                                @foreach($gallery as $index => $image)
                                    <button type="button"
                                            class="mx-product-detail__thumb @if($index === 0) is-active @endif"
                                            data-mx-gallery-thumb="{{ $image }}"
                                            aria-label="查看第 {{ $index + 1 }} 张">
                                        <img src="{{ $image }}" alt="" loading="lazy">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="mx-product-detail__stage mx-product-detail__stage--empty">
                            <i class="fa fa-picture-o"></i>
                            <span>暂无商品图片</span>
                        </div>
                    @endif
                </div>

                <div class="mx-product-detail__info">
                    @if($record['tags'] !== [])
                        <div class="mx-tag-row">
                            @foreach($record['tags'] as $tag)
                                <span class="mx-tag">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                    <h1 class="mx-product-detail__title">{{ $record['title'] !== '' ? $record['title'] : ($model['name'] ?? '商品详情') }}</h1>
                    @if($record['subtitle'] !== '')
                        <p class="mx-product-detail__subtitle">{{ $record['subtitle'] }}</p>
                    @endif
                    @if($record['summary'] !== '')
                        <p class="mx-product-detail__summary">{{ $record['summary'] }}</p>
                    @endif

                    <div class="mx-product-detail__price-box">
                        @if($record['price'] !== '')
                            <span class="mx-product-detail__price">{{ $record['price'] }}</span>
                            @if($record['origin_price'] !== '')
                                <del class="mx-price-origin">原价 {{ $record['origin_price'] }}</del>
                            @endif
                        @else
                            <span class="mx-product-detail__price mx-price--quote">价格面议</span>
                        @endif
                        @if($record['stock'] !== null)
                            <span class="mx-product-detail__stock @if($record['stock'] <= 0) is-out @endif">
                                {{ $record['stock'] > 0 ? '库存 ' . $record['stock'] : '暂时缺货' }}
                            </span>
                        @endif
                    </div>

                    @if($record['spec'] !== '')
                        <dl class="mx-product-detail__spec">
                            <dt>规格</dt>
                            <dd>{{ $record['spec'] }}</dd>
                        </dl>
                    @endif

                    <div class="mx-product-detail__actions">
                        <a class="mx-product__action mx-product__action--primary" href="javascript:void(0)" data-mx-inquiry>
                            <i class="fa fa-commenting-o"></i>
                            <span>咨询详情</span>
                        </a>
                        @if(model_list_url($access))
                            <a class="mx-product__action mx-product__action--ghost" href="{{ model_list_url($access) }}">
                                <i class="fa fa-angle-left"></i>
                                <span>返回列表</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <article class="mx-detail-main">
                @themeInclude('public.detailContent', [
                    'detailRecord' => $record['raw'],
                    'detailFields' => $detailFields,
                    'model' => $model,
                    'detailInteractions' => $detailInteractions ?? [],
                ])

                @themeInclude('public.articleNavigation')
            </article>
        </div>
    </div>
</div>
