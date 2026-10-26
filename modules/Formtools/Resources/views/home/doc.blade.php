<style>
    .ft-doc-workspace {
        --ft-doc-tree-w: 320px;
        display: grid;
        grid-template-columns: var(--ft-doc-tree-w) minmax(0, 1fr);
        gap: 24px;
        align-items: flex-start;
        max-width: 1200px;
        margin: 24px auto;
        padding: 0 16px;
    }
    .ft-doc-side {
        position: sticky;
        top: 16px;
        max-height: calc(100vh - 32px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.04);
    }
    .ft-doc-side__head {
        padding: 18px 18px 14px;
        border-bottom: 1px solid #edf2f7;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }
    .ft-doc-side__title {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.5;
    }
    .ft-doc-side__desc {
        margin: 6px 0 0;
        font-size: 12px;
        color: #64748b;
        line-height: 1.6;
    }
    .ft-doc-side__search {
        position: relative;
        margin-top: 12px;
    }
    .ft-doc-side__search input {
        width: 100%;
        padding: 8px 12px 8px 32px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
        font-size: 13px;
        transition: all 0.15s ease;
        box-sizing: border-box;
    }
    .ft-doc-side__search input:focus {
        outline: none;
        background: #fff;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }
    .ft-doc-side__search::before {
        content: "\e090";
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-family: 'icomoon';
        color: #94a3b8;
        font-size: 14px;
    }
    .ft-doc-tree {
        flex: 1;
        padding: 10px 8px 14px;
        overflow: auto;
        max-height: calc(100vh - 220px);
    }
    .ft-doc-tree__root,
    .ft-doc-node__children {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .ft-doc-node__children {
        padding-left: 14px;
    }
    .ft-doc-tree__contents {
        list-style: none;
        margin: 0;
        padding: 0 0 0 28px;
    }

    .ft-doc-article {
        background: #fff;
        border-radius: 12px;
        padding: 32px 40px 28px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    }
    .ft-doc-detail__title {
        font-size: 26px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.4;
        margin: 0 0 8px;
    }
    .ft-doc-detail__desc {
        font-size: 14px;
        color: #64748b;
        line-height: 1.7;
        margin: 0 0 16px;
    }
    .ft-doc-detail__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        padding: 12px 0 16px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 20px;
        font-size: 13px;
        color: #64748b;
    }
    .ft-doc-detail__meta-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .ft-doc-detail__meta-item i {
        font-size: 14px;
        color: #94a3b8;
    }
    .ft-doc-detail__meta-item--price {
        color: #d97706;
        font-weight: 700;
    }
    .ft-doc-detail__cover {
        margin: 0 0 24px;
        text-align: center;
    }
    .ft-doc-detail__cover img {
        max-width: 100%;
        max-height: 460px;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        cursor: zoom-in;
    }
    .ft-doc-detail__cover--masked {
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        color: #94a3b8;
        border-radius: 10px;
        font-size: 14px;
    }
    .ft-doc-detail__content {
        font-size: 15px;
        color: #1e293b;
        line-height: 1.85;
    }
    .ft-doc-detail__content p { margin: 0 0 12px; }
    .ft-doc-detail__content img { max-width: 100%; height: auto; border-radius: 6px; }
    .ft-doc-detail__content-mask-hint {
        color: #d97706;
        font-size: 13px;
        margin-top: 16px;
    }
    .ft-doc-detail__actions {
        display: flex;
        gap: 12px;
        align-items: center;
        justify-content: flex-end;
        padding: 16px 0;
        margin-top: 24px;
        border-top: 1px solid #e2e8f0;
    }
    .ft-doc-detail__buy-btn {
        display: inline-flex;
        align-items: center;
        padding: 8px 22px;
        border: 0;
        border-radius: 999px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
    }
    .ft-doc-detail__buy-btn:hover { opacity: 0.9; color: #fff; }
    .ft-doc-detail__extra {
        margin-top: 28px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 10px;
    }
    .ft-doc-detail__extra-title {
        font-size: 14px;
        font-weight: 700;
        color: #475569;
        margin: 0 0 12px;
    }
    .ft-doc-detail__extra-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 12px 20px;
    }
    .ft-doc-detail__extra-label {
        font-size: 12px;
        color: #94a3b8;
        margin-bottom: 2px;
    }
    .ft-doc-detail__extra-value {
        font-size: 13px;
        color: #1e293b;
        word-break: break-word;
    }
    .ft-doc-node--content .ft-doc-node__row {
        font-size: 12.5px;
        color: #475569;
        padding: 5px 10px;
    }
    .ft-doc-node--content .ft-doc-node__icon { color: #94a3b8; }
    .ft-doc-node--content .ft-doc-node__row.is-active {
        color: #1d4ed8;
        background: #eef2ff;
    }
    .ft-doc-node__row {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        margin: 1px 0;
        border-radius: 8px;
        cursor: pointer;
        color: #1e293b;
        font-size: 13px;
        line-height: 1.4;
        transition: background 0.12s ease;
        text-decoration: none;
    }
    a.ft-doc-node__row:hover {
        background: #f1f5f9;
        color: #1d4ed8;
    }
    .ft-doc-node__row.ft-doc-node__row--static {
        cursor: default;
        color: #475569;
    }
    .ft-doc-node__row.is-active {
        background: #eef2ff;
        color: #1d4ed8;
        font-weight: 600;
    }
    .ft-doc-node__caret {
        display: none;
    }
    .ft-doc-node__title {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ft-doc-node__price {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 999px;
        background: #fef3c7;
        color: #b45309;
        font-size: 11px;
        font-weight: 600;
        flex-shrink: 0;
    }
    .ft-doc-node__price--free {
        background: #dcfce7;
        color: #166534;
    }
    .ft-doc-main {
        min-height: 70vh;
    }
    .ft-doc-breadcrumb {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 13px;
        color: #64748b;
        margin-bottom: 14px;
    }
    .ft-doc-breadcrumb a {
        color: #6366f1;
        text-decoration: none;
    }
    .ft-doc-breadcrumb a:hover {
        text-decoration: underline;
    }
    .ft-doc-breadcrumb__sep {
        color: #cbd5e1;
    }
    .ft-doc-detail__title {
        margin: 0 0 8px;
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.4;
    }
    .ft-doc-detail__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 0 0 18px;
        font-size: 13px;
        color: #64748b;
    }
    .ft-doc-paid-bar {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        margin-bottom: 22px;
        border: 1px solid #fde68a;
        border-radius: 14px;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }
    .ft-doc-paid-bar--paid {
        border-color: #bbf7d0;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    }
    .ft-doc-paid-bar__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(245, 158, 11, 0.18);
        color: #b45309;
        font-size: 20px;
        flex-shrink: 0;
    }
    .ft-doc-paid-bar--paid .ft-doc-paid-bar__icon {
        background: rgba(34, 197, 94, 0.18);
        color: #166534;
    }
    .ft-doc-paid-bar__body { flex: 1; }
    .ft-doc-paid-bar__title { font-size: 14px; font-weight: 700; color: #92400e; }
    .ft-doc-paid-bar--paid .ft-doc-paid-bar__title { color: #166534; }
    .ft-doc-paid-bar__meta { margin-top: 2px; font-size: 12px; color: #b45309; }
    .ft-doc-paid-bar--paid .ft-doc-paid-bar__meta { color: #166534; }
    .ft-doc-paid-bar__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 18px;
        border-radius: 999px;
        background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 6px 16px rgba(234, 88, 12, 0.3);
        border: 0;
        cursor: pointer;
    }
    .ft-doc-paid-bar__btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(234, 88, 12, 0.4);
    }
    .ft-doc-field {
        display: grid;
        grid-template-columns: 140px minmax(0, 1fr);
        gap: 16px;
        padding: 14px 0;
        border-bottom: 1px solid #edf2f7;
    }
    .ft-doc-field__label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
    }
    .ft-doc-field__value {
        font-size: 14px;
        color: #1e293b;
        line-height: 1.7;
        word-break: break-word;
    }
    .ft-doc-field__value--html img { max-width: 100%; height: auto; }
    .ft-doc-field__value--image img {
        max-width: 320px;
        max-height: 320px;
        border-radius: 8px;
        border: 1px solid #edf2f7;
    }
    .ft-doc-field__value--empty {
        color: #cbd5e1;
        font-style: italic;
    }
    .ft-doc-empty {
        padding: 60px 20px;
        text-align: center;
        color: #94a3b8;
    }
    .ft-doc-empty__icon { font-size: 60px; color: #cbd5e1; margin-bottom: 12px; }
    .ft-doc-empty__title { font-size: 16px; font-weight: 600; color: #475569; margin: 0 0 6px; }
    .ft-doc-empty__desc { font-size: 13px; line-height: 1.7; margin: 0; }
    .ft-doc-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 0;
        border-bottom: 1px solid #edf2f7;
        margin-bottom: 22px;
        gap: 12px;
    }
    .ft-doc-toolbar__actions { display: flex; gap: 8px; }
    .ft-doc-toolbar__btn {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }
    .ft-doc-toolbar__btn:hover { background: #e2e8f0; color: #1e293b; }
    @media (max-width: 1199px) {
        .ft-doc-workspace { grid-template-columns: 280px minmax(0, 1fr); }
    }
    @media (max-width: 991px) {
        .ft-doc-workspace { grid-template-columns: 1fr; }
        .ft-doc-side { position: static; max-height: 380px; }
        .ft-doc-tree { max-height: 240px; }
    }
</style>
@php
    $treeData = $treeData ?? [];
    $selectedId = (int) ($selectedId ?? 0);
    $ps = $priceStatus ?? [];
    $isPaid = !empty($ps['is_paid']);
    $needPay = !empty($ps['need_pay']);
    $price = (float) ($ps['price'] ?? 0);
    $isLogin = !empty($ps['is_login']);
    $payUrl = (string) ($ps['pay_url'] ?? '');
    $access = (string) ($param['model'] ?? '');

    $rawRecord = is_object($data) ? get_object_vars($data) : (is_array($data) ? $data : []);
    $detailRecord = is_array($detailRecord) ? $detailRecord : $rawRecord;
    $record = $detailRecord + $rawRecord;
    $rawRecord = $record;
    $contentTitle = (string) ($record['title'] ?? ($record['name'] ?? ''));
    $docTitle = (string) ($frontDocTitle ?? $contentTitle);
    $docDesc = (string) ($frontDocDesc ?? '');
    $createdAt = (string) ($record['created_at'] ?? '');
    $accessCount = (int) ($record['access_count'] ?? 0);
    $docStatus = (string) ($record['status'] ?? '1');

    $titleIdents = ['title', 'name'];
    $coverIdents = ['cover', 'image', 'pic', 'thumb', 'thumbnail', 'banner', 'cover_image', 'cover_url', 'pic_url'];
    $contentIdents = ['content', 'body', 'article', 'article_content', 'detail', 'html', 'description', 'content_html', 'article_body', 'main_content'];
    $dateIdents = ['created_at', 'updated_at', 'publish_time', 'release_time', 'datetime', 'date'];
    $accessIdents = ['access_count', 'view_count', 'read_count', 'click_count', 'hits', 'views'];
    $goodIdents = ['good_count', 'like_count', 'praise_count', 'star_count', 'favorite_count', 'collect_count'];
    $commentIdents = ['comment_count', 'reply_count'];
    $downloadIdents = ['download_count', 'down_count'];
    $authorIdents = ['author', 'author_name', 'username', 'nickname', 'user_name', 'editor', 'source'];
    $skipColumns = ['id', 'pid', 'uid', 'status', 'remark', 'price', 'sort', 'sorts', 'created_at', 'updated_at',
        'seo_title', 'seo_keywords', 'seo_description', 'access_count', 'good_count', 'comment_count', 'download_count',
        'prev_item', 'next_item', 'prev_id', 'next_id', 'last_id', 'price_status', 'fields'];

    $modelFieldsSchema = isset($model) && isset($model['fields']) && is_array($model['fields']) ? $model['fields'] : [];

    // 1) 优先从 schema 中识别结构化字段
    $coverField = null;
    $contentField = null;
    $otherFields = [];
    foreach ($modelFieldsSchema as $field) {
        $ident = (string) ($field['identification'] ?? '');
        $formtype = (string) ($field['formtype'] ?? '');
        $name = (string) ($field['name'] ?? $ident);
        $datas = (array) ($field['datas'] ?? []);
        $raw = (string) ($record[$ident] ?? '');
        $display = $raw;
        $isImage = in_array($formtype, ['image', 'imageAjax'], true);
        $isFile = in_array($formtype, ['upload', 'uploadAjax'], true);
        $isEditor = $formtype === 'editor';

        if ($coverField === null && ($isImage || in_array($ident, $coverIdents, true)) && $raw !== '' && !in_array($ident, $titleIdents, true)) {
            $coverField = ['identification' => $ident, 'name' => $name, 'raw' => $raw];
        }
        if ($contentField === null && ($isEditor || in_array($ident, $contentIdents, true)) && $raw !== '') {
            $contentField = ['identification' => $ident, 'name' => $name, 'raw' => $raw, 'is_editor' => $isEditor, 'is_masked' => $needPay && $price > 0];
        }
        if ($ident === '' || in_array($ident, $skipColumns, true)) {
            continue;
        }
        if (!$isEditor && !$isImage && !$isFile && $raw !== '' && isset($datas[$raw])) {
            $display = (string) $datas[$raw];
        } elseif (!$isEditor && !$isImage && !$isFile && $raw !== '' && isset($datas[(string) $raw])) {
            $display = (string) $datas[(string) $raw];
        }
        $otherFields[] = [
            'identification' => $ident,
            'name' => $name,
            'formtype' => $formtype,
            'raw' => $raw,
            'display' => $display,
            'is_image' => $isImage,
            'is_file' => $isFile,
            'is_editor' => $isEditor,
            'is_masked' => $needPay && $price > 0,
        ];
    }

    // 2) 兜底：如果 schema 没识别到封面/正文，再从 record 字段名中查找
    if ($coverField === null) {
        foreach ($coverIdents as $cIdent) {
            if (isset($record[$cIdent]) && !empty($record[$cIdent]) && !in_array($cIdent, $titleIdents, true)) {
                $coverField = ['identification' => $cIdent, 'name' => $cIdent, 'raw' => (string) $record[$cIdent]];
                break;
            }
        }
    }
    if ($contentField === null) {
        foreach ($contentIdents as $cIdent) {
            if (isset($record[$cIdent]) && !empty($record[$cIdent])) {
                $contentField = ['identification' => $cIdent, 'name' => $cIdent, 'raw' => (string) $record[$cIdent], 'is_editor' => false, 'is_masked' => $needPay && $price > 0];
                break;
            }
        }
    }

    // 3) meta 字段值提取
    $pickFirst = function (array $candidates) use ($record) {
        foreach ($candidates as $key) {
            if (isset($record[$key]) && $record[$key] !== '' && $record[$key] !== null) {
                return ['key' => $key, 'value' => $record[$key]];
            }
        }
        return null;
    };
    $metaCreated = $pickFirst($dateIdents);
    $metaAccess = $pickFirst($accessIdents);
    $metaGood = $pickFirst($goodIdents);
    $metaComment = $pickFirst($commentIdents);
    $metaDownload = $pickFirst($downloadIdents);
    $metaAuthor = $pickFirst($authorIdents);

    $formatDate = function ($val) {
        if ($val === null || $val === '') return '';
        if (is_numeric($val)) {
            $ts = (int) $val;
            if ($ts > 1000000000 && $ts < 2000000000) {
                return date('Y-m-d H:i', $ts);
            }
        }
        $t = strtotime((string) $val);
        if ($t) {
            return date('Y-m-d H:i', $t);
        }
        return (string) $val;
    };
    $hasAnyValue = !empty($coverField) || !empty($contentField) || !empty($contentTitle) || $accessCount > 0 || $createdAt !== '';
@endphp
<div class="ft-doc-workspace">
    <aside class="ft-doc-side">
        <div class="ft-doc-side__head">
            <h2 class="ft-doc-side__title">{{ $docTitle ?: ($model['name'] ?? '文档工作台') }}</h2>
            @if($docDesc !== '')
                <p class="ft-doc-side__desc">{{ $docDesc }}</p>
            @endif
            <div class="ft-doc-side__search">
                <input type="text" id="ftDocSearch" placeholder="搜索文档标题…" autocomplete="off">
            </div>
        </div>
        <div class="ft-doc-tree" id="ftDocTree">
            @if(empty($treeData))
                <div style="padding: 24px 12px; text-align: center; color: #94a3b8; font-size: 13px;">
                    暂无文档内容
                </div>
            @else
                @include('formtools::home._docTreeNode', [
                    'nodes' => $treeData,
                    'level' => 0,
                    'selectedId' => $selectedId,
                    'activeCategoryId' => $activeCategoryId ?? 0,
                    'access' => $access,
                    'wrapperClass' => 'ft-doc-tree__root',
                ])
            @endif
        </div>
    </aside>
    <section class="ft-doc-main">
        <div class="ft-doc-toolbar">
            <div class="ft-doc-breadcrumb" id="ftDocBreadcrumb">
                <a href="{{ model_list_url($access) }}">{{ $model['name'] ?? '文档库' }}</a>
                <span class="ft-doc-breadcrumb__sep">/</span>
                <span style="color: #0f172a; font-weight: 600;">{{ $contentTitle }}</span>
            </div>
            <div class="ft-doc-toolbar__actions">
                @if(!empty($detailInteractions['prev']) || !empty($detailRecord['prev_item']))
                    <a href="{{ !empty($detailRecord['prev_item']) ? model_detail_url($access, $detailRecord['prev_item']['id']) : '#' }}" class="ft-doc-toolbar__btn">← 上一篇</a>
                @endif
                @if(!empty($detailRecord['next_item']))
                    <a href="{{ model_detail_url($access, $detailRecord['next_item']['id']) }}" class="ft-doc-toolbar__btn">下一篇 →</a>
                @endif
            </div>
        </div>
        @if($needPay)
            <div class="ft-doc-paid-bar">
                <div class="ft-doc-paid-bar__icon"><i class="icon-lock"></i></div>
                <div class="ft-doc-paid-bar__body">
                    <div class="ft-doc-paid-bar__title">本文档为付费内容</div>
                    <div class="ft-doc-paid-bar__meta">
                        <span style="font-size:16px;font-weight:800;">￥{{ number_format($price, 2) }}</span>
                        <span style="margin: 0 6px; opacity: 0.6;">·</span>
                        一次性买断，永久访问
                    </div>
                </div>
                <div>
                    @if($isLogin)
                        <button type="button" class="ft-doc-paid-bar__btn" id="ftDocBuy" data-pay-url="{{ $payUrl }}">立即购买</button>
                    @else
                        <a href="{{ \App\Support\Auth\HomeLoginRedirect::loginUrl() }}" class="ft-doc-paid-bar__btn">登录后购买</a>
                    @endif
                </div>
            </div>
            <div style="position: relative; padding: 28px 20px; margin: 8px 0 20px; text-align: center; background: linear-gradient(180deg, rgba(255,251,235,0.6) 0%, rgba(254,243,199,0.85) 100%); border-radius: 12px; border: 1px dashed #fcd34d;">
                <div style="font-size:32px;color:#d97706;margin-bottom:6px;"><i class="icon-lock"></i></div>
                <div style="font-size:15px;font-weight:700;color:#92400e;">已隐藏部分内容</div>
                <div style="font-size:13px;color:#b45309;margin-top:4px;">购买后即可查看完整内容</div>
            </div>
        @elseif($price > 0 && $isPaid)
            <div class="ft-doc-paid-bar ft-doc-paid-bar--paid">
                <div class="ft-doc-paid-bar__icon"><i class="icon-checkmark-circle"></i></div>
                <div class="ft-doc-paid-bar__body">
                    <div class="ft-doc-paid-bar__title">您已购买此内容</div>
                    <div class="ft-doc-paid-bar__meta">已支付 ￥{{ number_format($price, 2) }}，可永久查看完整内容</div>
                </div>
            </div>
        @endif
        <article class="ft-doc-article">
            <h1 class="ft-doc-detail__title">{{ $contentTitle }}</h1>
            @if($docDesc !== '')
                <p class="ft-doc-detail__desc">{{ $docDesc }}</p>
            @endif
            <div class="ft-doc-detail__meta">
                @if($metaCreated)
                    <span class="ft-doc-detail__meta-item"><i class="icon-clock3"></i> {{ $formatDate($metaCreated['value']) }}</span>
                @endif
                @if($metaAuthor)
                    <span class="ft-doc-detail__meta-item"><i class="icon-user"></i> {{ $metaAuthor['value'] }}</span>
                @endif
                @if($metaAccess)
                    <span class="ft-doc-detail__meta-item"><i class="icon-eye"></i> 阅读 {{ (int) $metaAccess['value'] }}</span>
                @endif
                @if($metaComment)
                    <span class="ft-doc-detail__meta-item"><i class="icon-bubble"></i> 评论 {{ (int) $metaComment['value'] }}</span>
                @endif
                @if($metaDownload)
                    <span class="ft-doc-detail__meta-item"><i class="icon-download"></i> 下载 {{ (int) $metaDownload['value'] }}</span>
                @endif
                @if($price > 0)
                    <span class="ft-doc-detail__meta-item ft-doc-detail__meta-item--price">￥{{ number_format($price, 2) }}</span>
                @endif
            </div>

            @if(!$hasAnyValue && empty($coverField) && empty($contentField))
                <div class="ft-doc-empty">
                    <div class="ft-doc-empty__icon"><i class="icon-file-text2"></i></div>
                    <h3 class="ft-doc-empty__title">本篇文档暂无字段内容</h3>
                    <p class="ft-doc-empty__desc">该动态模型当前没有可显示的字段值。</p>
                </div>
            @else
                @if(!empty($coverField) && empty($coverField['is_masked']))
                    <div class="ft-doc-detail__cover">
                        <img src="{{ GetUrlByPath($coverField['raw']) }}" alt="{{ $contentTitle }}" loading="lazy" onclick="if(window.ftDocImageView) ftDocImageView(this.src);">
                    </div>
                @elseif(!empty($coverField) && !empty($coverField['is_masked']) && $price > 0)
                    <div class="ft-doc-detail__cover ft-doc-detail__cover--masked">封面图（购买后查看）</div>
                @endif

                @if(!empty($contentField))
                    <div class="ft-doc-detail__content">
                        @if(!empty($contentField['is_masked']) && $price > 0)
                            @php $plain = strip_tags($contentField['raw']); $snippet = mb_substr($plain, 0, 240); @endphp
                            <p>{{ $snippet }}{{ mb_strlen($plain) > 240 ? '…' : '' }}</p>
                            <p class="ft-doc-detail__content-mask-hint">购买后查看完整内容</p>
                        @elseif($contentField['is_editor'])
                            {!! $contentField['raw'] !!}
                        @else
                            <p>{{ $contentField['raw'] }}</p>
                        @endif
                    </div>
                @endif

                <div class="ft-doc-detail__actions">
                    @if(!empty($detailInteractions['can_like']))
                        <button type="button"
                                class="mx-detail-action-btn js-detail-like ft-doc-detail__like-btn"
                                data-url="{{ $detailInteractions['like_url'] }}"
                                data-initial-text="点赞支持"
                                data-loading-text="提交中">
                            <i class="fa fa-thumbs-up"></i>
                            <span>点赞支持</span>
                            <strong data-like-count>{{ (int) ($record['good_count'] ?? 0) }}</strong>
                        </button>
                    @endif
                    @if($price > 0 && !$isPaid && $isLogin)
                        <button type="button" class="ft-doc-detail__buy-btn" id="ftDocBuyInline" data-pay-url="{{ $payUrl }}">立即购买 ￥{{ number_format($price, 2) }}</button>
                    @elseif($price > 0 && !$isPaid && !$isLogin)
                        <a href="{{ \App\Support\Auth\HomeLoginRedirect::loginUrl() }}" class="ft-doc-detail__buy-btn">登录后购买 ￥{{ number_format($price, 2) }}</a>
                    @endif
                </div>

                @if(!empty($detailInteractions['can_like']))
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            document.querySelectorAll('.js-detail-like').forEach(function (likeButton) {
                                if (likeButton.__likeBound) return;
                                likeButton.__likeBound = true;
                                likeButton.addEventListener('click', function () {
                                    if (likeButton.disabled) return;
                                    var url = likeButton.getAttribute('data-url');
                                    var tokenNode = document.getElementById('token');
                                    var likeCountNode = likeButton.querySelector('[data-like-count]');
                                    var originalText = likeButton.getAttribute('data-initial-text') || '点赞支持';
                                    var loadingText = likeButton.getAttribute('data-loading-text') || '提交中';
                                    likeButton.disabled = true;
                                    likeButton.classList.add('is-loading');
                                    likeButton.querySelector('span').textContent = loadingText;
                                    fetch(url, {
                                        method: 'POST',
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'X-CSRF-TOKEN': tokenNode ? tokenNode.getAttribute('content') : '',
                                            'Accept': 'application/json'
                                        }
                                    }).then(function (response) { return response.json(); })
                                      .then(function (result) {
                                          if (result && result.status === 200 && result.data && likeCountNode) {
                                              likeCountNode.textContent = result.data.good_count || 0;
                                          } else {
                                              throw new Error((result && result.msg) || '点赞失败');
                                          }
                                      })
                                      .catch(function (error) {
                                          if (window.layer && typeof window.layer.msg === 'function') {
                                              window.layer.msg(error.message || '点赞失败');
                                          }
                                      })
                                      .finally(function () {
                                          likeButton.disabled = false;
                                          likeButton.classList.remove('is-loading');
                                          likeButton.querySelector('span').textContent = originalText;
                                      });
                                });
                            });
                        });
                    </script>
                @endif

                @php
                    $otherValueFields = array_values(array_filter($otherFields, function ($f) {
                        if ($f['raw'] === '') return false;
                        if (in_array($f['identification'], ['title', 'name', 'cover', 'image', 'pic', 'thumb', 'thumbnail', 'banner', 'cover_image', 'cover_url', 'pic_url'], true)) return false;
                        if (in_array($f['identification'], ['content', 'body', 'article', 'article_content', 'detail', 'html', 'description', 'content_html', 'article_body', 'main_content'], true)) return false;
                        if (in_array($f['identification'], ['created_at', 'updated_at', 'publish_time', 'release_time', 'datetime', 'date'], true)) return false;
                        if (in_array($f['identification'], ['access_count', 'view_count', 'read_count', 'click_count', 'hits', 'views'], true)) return false;
                        if (in_array($f['identification'], ['good_count', 'like_count', 'praise_count', 'star_count', 'favorite_count', 'collect_count'], true)) return false;
                        if (in_array($f['identification'], ['comment_count', 'reply_count'], true)) return false;
                        if (in_array($f['identification'], ['download_count', 'down_count'], true)) return false;
                        if (in_array($f['identification'], ['author', 'author_name', 'username', 'nickname', 'user_name', 'editor', 'source'], true)) return false;
                        return true;
                    }));
                @endphp
                @if(!empty($otherValueFields))
                    <div class="ft-doc-detail__extra">
                        <h3 class="ft-doc-detail__extra-title">附加信息</h3>
                        <div class="ft-doc-detail__extra-grid">
                            @foreach($otherValueFields as $f)
                                <div class="ft-doc-detail__extra-item">
                                    <div class="ft-doc-detail__extra-label">{{ $f['name'] }}</div>
                                    <div class="ft-doc-detail__extra-value">
                                        @if($f['is_image'])
                                            <img src="{{ GetUrlByPath($f['raw']) }}" alt="{{ $f['name'] }}" loading="lazy" onclick="if(window.ftDocImageView) ftDocImageView(this.src);" style="max-width:100%;border-radius:6px;">
                                        @elseif($f['is_file'])
                                            <a href="{{ GetUrlByPath($f['raw']) }}" target="_blank" rel="noopener"><i class="icon-file-download2"></i> 下载附件</a>
                                        @else
                                            {{ $f['display'] ?: $f['raw'] }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </article>
    </section>
</div>
<script>
    window.ftDocImageView = function (src) {
        if (typeof layer === 'undefined') {
            window.open(src);
            return;
        }
        layer.open({
            type: 1,
            title: false,
            closeBtn: 1,
            shadeClose: true,
            content: '<div style="padding: 16px; background: #0f172a; text-align: center;"><img src="' + src + '" style="max-width: 90vw; max-height: 90vh; display: inline-block;"></div>'
        });
    };
    (function () {
        var tree = document.getElementById('ftDocTree');
        if (!tree) return;
        var searchInput = document.getElementById('ftDocSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var q = searchInput.value.trim().toLowerCase();
                document.querySelectorAll('#ftDocTree .ft-doc-node').forEach(function (node) {
                    var titleEl = node.querySelector(':scope > .ft-doc-node__row .ft-doc-node__title');
                    if (!titleEl) return;
                    var text = titleEl.textContent.toLowerCase();
                    if (q === '' || text.indexOf(q) !== -1) {
                        node.style.display = '';
                    } else {
                        node.style.display = 'none';
                    }
                });
            });
        }
        var buyInline = document.getElementById('ftDocBuyInline');
        if (buyInline) {
            function getCsrf() {
                var el = document.getElementById('token');
                return el ? (el.getAttribute('content') || '') : '';
            }
            buyInline.addEventListener('click', function () {
                var payUrl = buyInline.getAttribute('data-pay-url');
                if (!payUrl) return;
                if (typeof layer === 'undefined') {
                    window.location.href = payUrl;
                    return;
                }
                var csrf = getCsrf();
                var loading = layer.msg('正在创建订单…', { icon: 16, time: 0 });
                fetch(payUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: '_token=' + encodeURIComponent(csrf) + '&pay_method=WeChat&pay_type=public'
                }).then(function (r) { return r.json(); })
                  .then(function (resp) {
                      layer.close(loading);
                      if (!resp || resp.code !== 200) {
                          layer.msg((resp && resp.msg) || '创建订单失败');
                          return;
                      }
                      var pay = resp.data || {};
                      if (pay.pay_url) {
                          window.location.href = pay.pay_url;
                      } else if (pay.qrcode) {
                          layer.open({
                              type: 1,
                              title: '扫码支付',
                              area: ['280px', '320px'],
                              content: '<div style="text-align:center;padding:20px;"><img src="' + pay.qrcode + '" style="width:200px;height:200px;"><div style="margin-top:12px;color:#666;">订单号：' + (pay.order_no || '') + '</div></div>'
                          });
                      } else {
                          layer.msg('已创建订单，请到订单中心完成支付');
                      }
                  })
                  .catch(function () {
                      layer.close(loading);
                      layer.msg('网络异常');
                  });
            });
        }
        var buyBtn = document.getElementById('ftDocBuy');
        if (buyBtn) {
            buyBtn.addEventListener('click', function () {
                var payUrl = buyBtn.getAttribute('data-pay-url');
                if (typeof layer === 'undefined') {
                    window.location.href = payUrl;
                    return;
                }
                var loading = layer.msg('正在创建订单…', { icon: 16, time: 0 });
                fetch(payUrl, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pay_method: 'WeChat', pay_type: 'public' })
                }).then(function (r) { return r.json(); })
                  .then(function (resp) {
                      layer.close(loading);
                      if (!resp || resp.code !== 200) {
                          layer.msg((resp && resp.msg) || '创建订单失败');
                          return;
                      }
                      var pay = resp.data || {};
                      if (pay.pay_url) {
                          window.location.href = pay.pay_url;
                      } else if (pay.qrcode) {
                          layer.open({
                              type: 1,
                              title: '扫码支付',
                              area: ['280px', '320px'],
                              content: '<div style="text-align:center;padding:20px;"><img src="' + pay.qrcode + '" style="width:200px;height:200px;"><div style="margin-top:12px;color:#666;">订单号：' + (pay.order_no || '') + '</div></div>'
                          });
                      } else {
                          layer.msg('已创建订单，请到订单中心完成支付');
                      }
                  })
                  .catch(function () {
                      layer.close(loading);
                      layer.msg('网络异常，请稍后重试');
                  });
            });
        }
    })();
</script>
