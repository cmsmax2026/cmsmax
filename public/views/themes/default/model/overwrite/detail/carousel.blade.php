<div id="content">
    <div class="container">
        <div class="mx-card-list">
            @forelse($data as $item)
                <a href="{{ model_detail_url($param['model'], $item['id']) }}" class="mx-card text-none-decoration">
                    @if(!empty($item['cover']))
                        <div class="mx-card__thumb" style="aspect-ratio: 16 / 6;">
                            <img src="{{ GetUrlByPath($item['cover']) }}" alt="{{ $item['title'] ?? 'carousel' }}">
                        </div>
                    @endif
                    <h3 class="mx-card__title">{{ $item['title'] ?? themeTrans('detail.carousel_no') }}</h3>
                    <p class="mx-card__desc">{{ \Illuminate\Support\Str::limit(strip_tags($item['content'] ?? ''), 120) }}</p>
                </a>
            @empty
                <div class="mx-empty">{{ themeTrans('detail.empty_carousel') }}</div>
            @endforelse
        </div>
    </div>
</div>
