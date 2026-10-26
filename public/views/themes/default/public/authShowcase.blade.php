@php
    $authEyebrow = $authEyebrow ?? 'Account Center';
    $authTitle = $authTitle ?? themeTrans('showcase.title');
    $authDescription = $authDescription ?? themeTrans('showcase.description');
    $authHighlights = $authHighlights ?? [
        themeTrans('showcase.highlight_1'),
        themeTrans('showcase.highlight_2'),
        themeTrans('showcase.highlight_3'),
    ];
@endphp

<div class="mx-auth-showcase">
    <span class="mx-auth-showcase__eyebrow">{{ $authEyebrow }}</span>
    <h1 class="mx-auth-showcase__title">{{ $authTitle }}</h1>
    <p class="mx-auth-showcase__description">{{ $authDescription }}</p>

    <div class="mx-auth-showcase__list">
        @foreach($authHighlights as $highlight)
            <div class="mx-auth-showcase__item">
                <i class="fa fa-check-circle"></i>
                <span>{{ $highlight }}</span>
            </div>
        @endforeach
    </div>

    <div class="mx-auth-showcase__meta">
        <span><i class="fa fa-shield"></i> {{ themeTrans('showcase.secure') }}</span>
        <span><i class="fa fa-bolt"></i> {{ themeTrans('showcase.unified') }}</span>
        <span><i class="fa fa-user-circle"></i> {{ themeTrans('showcase.member_center') }}</span>
    </div>
</div>
