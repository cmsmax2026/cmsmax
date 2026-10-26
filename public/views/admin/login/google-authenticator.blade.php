<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ cacheGlobalSettingsByKey('base_name') }} - {{ $pageTitle }}</title>
    <link rel="icon" type="image/ico" sizes="48x48" href="{{ GetLocalFileByPath(cacheGlobalSettingsByKey('webicon')) }}">
    <link href="{{ ADMIN_ASSET }}other/jqueryToast/css/toast.style.css" rel="stylesheet">
    <link href="{{ commonAsset('lib/bootstrap/admin/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ ADMIN_ASSET }}css/style.css" rel="stylesheet">
    <style>
        html, body {
            min-height: 100%;
        }

        body {
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 28%),
                radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.12), transparent 24%),
                linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        }

        .ga-shell {
            padding: 42px 0 56px;
        }

        .ga-main {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(320px, 0.9fr);
            gap: 22px;
            align-items: start;
        }

        .ga-panel,
        .ga-side {
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(12px);
        }

        .ga-panel {
            overflow: hidden;
        }

        .ga-hero {
            position: relative;
            padding: 28px 30px 26px;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #2563eb 100%);
            color: #fff;
        }

        .ga-hero:after {
            content: '';
            position: absolute;
            right: -36px;
            top: -36px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }

        .ga-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .ga-hero__title {
            position: relative;
            z-index: 1;
            margin: 18px 0 10px;
            font-size: 30px;
            line-height: 1.2;
            font-weight: 700;
        }

        .ga-hero__desc {
            position: relative;
            z-index: 1;
            max-width: 760px;
            color: rgba(255, 255, 255, 0.84);
            line-height: 1.8;
        }

        .ga-hero__meta {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 22px;
        }

        .ga-hero__meta-item {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .ga-hero__meta-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.68);
        }

        .ga-hero__meta-value {
            margin-top: 8px;
            font-size: 18px;
            font-weight: 700;
            word-break: break-word;
        }

        .ga-body {
            padding: 28px 30px 30px;
        }

        .ga-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .ga-block {
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .ga-block--full {
            grid-column: 1 / -1;
        }

        .ga-block__title {
            margin: 0 0 6px;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }

        .ga-block__desc {
            color: #64748b;
            line-height: 1.75;
            font-size: 13px;
        }

        .ga-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .ga-badge.is-active {
            color: #166534;
            background: #dcfce7;
        }

        .ga-badge.is-inactive {
            color: #92400e;
            background: #fef3c7;
        }

        .ga-codebox,
        .ga-input,
        .ga-textarea {
            width: 100%;
            border: 1px solid #d7deea;
            border-radius: 14px;
            background: #f8fafc;
            transition: all .2s ease;
        }

        .ga-codebox,
        .ga-input {
            min-height: 48px;
            padding: 12px 14px;
        }

        .ga-input:focus,
        .ga-textarea:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
            outline: none;
            background: #fff;
        }

        .ga-textarea {
            min-height: 108px;
            padding: 12px 14px;
            resize: vertical;
        }

        .ga-field + .ga-field {
            margin-top: 16px;
        }

        .ga-field__label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .ga-field__help {
            margin-top: 8px;
            color: #64748b;
            line-height: 1.75;
            font-size: 13px;
        }

        .ga-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 22px;
        }

        .ga-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none !important;
            transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
        }

        .ga-btn:hover {
            transform: translateY(-1px);
        }

        .ga-btn--primary {
            color: #fff;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
        }

        .ga-btn--ghost {
            color: #0f172a;
            background: #fff;
            border: 1px solid #d7deea;
        }

        .ga-btn--danger {
            color: #fff;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            box-shadow: 0 12px 24px rgba(220, 38, 38, 0.2);
        }

        .ga-qr-layout {
            display: grid;
            grid-template-columns: 244px minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .ga-qr {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 244px;
            padding: 12px;
            border: 1px dashed #cbd5e1;
            border-radius: 20px;
            background: #fff;
        }

        .ga-qr img {
            display: block;
            width: 220px;
            height: 220px;
            border-radius: 12px;
        }

        .ga-list {
            margin: 0;
            padding-left: 18px;
            color: #475569;
            line-height: 1.85;
        }

        .ga-side {
            padding: 22px;
        }

        .ga-side + .ga-side {
            margin-top: 18px;
        }

        .ga-side__title {
            margin: 0 0 8px;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }

        .ga-side__desc,
        .ga-side__meta {
            color: #64748b;
            line-height: 1.8;
        }

        .ga-side__meta strong {
            color: #0f172a;
        }

        .ga-side__steps {
            display: grid;
            gap: 12px;
            margin-top: 14px;
        }

        .ga-step {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            gap: 12px;
            align-items: start;
        }

        .ga-step__index {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 14px;
            font-weight: 700;
        }

        .ga-step__body {
            padding-top: 3px;
            color: #475569;
            line-height: 1.75;
        }

        .ga-step__title {
            margin: 0 0 4px;
            color: #0f172a;
            font-weight: 700;
            font-size: 14px;
        }

        @media (max-width: 1199px) {
            .ga-main {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .ga-shell {
                padding: 20px 0 34px;
            }

            .ga-hero,
            .ga-body,
            .ga-side {
                padding: 20px;
            }

            .ga-grid,
            .ga-hero__meta,
            .ga-qr-layout {
                grid-template-columns: 1fr;
            }

            .ga-hero__title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body class="bg-light">
@php($modeLabel = $mode === 'verify' ? getTranslateByKey('main_ga_mode_verify') : ($mode === 'setup' ? getTranslateByKey('main_ga_mode_setup') : getTranslateByKey('main_ga_mode_manage')))
@php($policyLabel = $googleAuthenticatorMode === 'required' ? getTranslateByKey('main_ga_policy_required') : ($googleAuthenticatorMode === 'optional' ? getTranslateByKey('main_ga_policy_optional') : getTranslateByKey('main_ga_policy_off')))
@php($heroDesc = $mode === 'verify'
    ? getTranslateByKey('main_ga_hero_verify')
    : ($mode === 'setup'
        ? getTranslateByKey('main_ga_hero_setup')
        : getTranslateByKey('main_ga_hero_manage')))
<div class="ga-shell">
    <div class="container">
        <div class="ga-main">
            <div class="ga-panel">
                <div class="ga-hero">
                    <div class="ga-hero__eyebrow">{{getTranslateByKey('main_ga_center_title')}}</div>
                    <div class="ga-hero__title">{{ $pageTitle }}</div>
                    <div class="ga-hero__desc">{{ $heroDesc }}</div>
                    <div class="ga-hero__meta">
                        <div class="ga-hero__meta-item">
                            <div class="ga-hero__meta-label">{{getTranslateByKey('main_ga_current_account')}}</div>
                            <div class="ga-hero__meta-value">{{ $adminUser['username'] ?? ('UID:' . ($adminUser['uid'] ?? 0)) }}</div>
                        </div>
                        <div class="ga-hero__meta-item">
                            <div class="ga-hero__meta-label">{{getTranslateByKey('main_ga_page_status')}}</div>
                            <div class="ga-hero__meta-value">{{ $modeLabel }}</div>
                        </div>
                        <div class="ga-hero__meta-item">
                            <div class="ga-hero__meta-label">{{getTranslateByKey('main_ga_global_policy')}}</div>
                            <div class="ga-hero__meta-value">{{ $policyLabel }}</div>
                        </div>
                    </div>
                </div>

                <div class="ga-body">
                    <div class="ga-grid">
                        @if($mode !== 'verify')
                            <div class="ga-block">
                                <h3 class="ga-block__title">{{getTranslateByKey('main_ga_binding_status')}}</h3>
                                <div class="ga-block__desc">{{getTranslateByKey('main_ga_binding_status_desc')}}</div>
                                <div style="margin-top:14px;">
                                    <span class="ga-badge {{ $hasBinding ? 'is-active' : 'is-inactive' }}">{{ $hasBinding ? getTranslateByKey('main_ga_bound') : getTranslateByKey('main_ga_unbound') }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="ga-block">
                            <h3 class="ga-block__title">{{getTranslateByKey('main_ga_policy_title')}}</h3>
                            <div class="ga-block__desc">
                                {{getTranslateByKey('main_ga_policy_desc')}}
                            </div>
                        </div>

                        @if(!$hasBinding)
                            <div class="ga-block ga-block--full">
                                <h3 class="ga-block__title">{{getTranslateByKey('main_ga_scan_or_manual')}}</h3>
                                <div class="ga-block__desc">{{getTranslateByKey('main_ga_scan_or_manual_desc')}}</div>
                                <div class="ga-qr-layout" style="margin-top:18px;">
                                    <div class="ga-qr">
                                        @if($qrPreviewUrl)
                                            <img src="{{ $qrPreviewUrl }}" alt="Google Authenticator QR">
                                        @else
                                            <div class="ga-block__desc">{{getTranslateByKey('main_ga_qr_unavailable')}}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="ga-field">
                                            <label class="ga-field__label">{{getTranslateByKey('main_ga_manual_secret')}}</label>
                                            <textarea class="ga-textarea" readonly>{{ $secret }}</textarea>
                                            <div class="ga-field__help">{{getTranslateByKey('main_ga_manual_secret_help')}}</div>
                                        </div>
                                        <div class="ga-field">
                                            <label class="ga-field__label">otpauth URI</label>
                                            <textarea class="ga-textarea" readonly>{{ $provisioningUri }}</textarea>
                                            <div class="ga-field__help">{{getTranslateByKey('main_ga_otpauth_help')}}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($mode === 'setup' || ($mode === 'manage' && !$hasBinding))
                            <div class="ga-block ga-block--full">
                                <h3 class="ga-block__title">{{ $isPendingLogin ? getTranslateByKey('main_ga_complete_bind_login') : getTranslateByKey('main_ga_bind_title') }}</h3>
                                <div class="ga-block__desc">{{getTranslateByKey('main_ga_bind_desc')}}</div>
                                <form method="post" action="{{ $bindAction }}">
                                    {{ csrf_field() }}
                                    <div class="ga-field" style="margin-top:18px;">
                                        <label class="ga-field__label">{{getTranslateByKey('main_ga_code_label')}}</label>
                                        <input class="ga-input" type="text" name="google_authenticator_code" maxlength="6" placeholder="{{getTranslateByKey('main_ga_enter_code')}}" autocomplete="one-time-code">
                                        <div class="ga-field__help">{{getTranslateByKey('main_ga_code_help')}}</div>
                                    </div>
                                    <div class="ga-actions">
                                        <button type="submit" class="ga-btn ga-btn--primary">{{ $isPendingLogin ? getTranslateByKey('main_ga_bind_and_login') : getTranslateByKey('main_ga_bind_now') }}</button>
                                        @if($mode === 'manage')
                                            <a href="{{ url('admin/system/secure/secureConfig') }}" class="ga-btn ga-btn--ghost">{{getTranslateByKey('main_ga_back_security')}}</a>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        @endif

                        @if($mode === 'verify')
                            <div class="ga-block ga-block--full">
                                <h3 class="ga-block__title">{{getTranslateByKey('main_ga_enter_code_title')}}</h3>
                                <div class="ga-block__desc">{{getTranslateByKey('main_ga_verify_desc')}}</div>
                                <form method="post" action="{{ $confirmAction }}">
                                    {{ csrf_field() }}
                                    <div class="ga-field" style="margin-top:18px;">
                                        <label class="ga-field__label">{{getTranslateByKey('main_ga_code_label')}}</label>
                                        <input class="ga-input" type="text" name="google_authenticator_code" maxlength="6" placeholder="{{getTranslateByKey('main_ga_enter_code')}}" autocomplete="one-time-code">
                                    </div>
                                    <div class="ga-actions">
                                        <button type="submit" class="ga-btn ga-btn--primary">{{getTranslateByKey('main_ga_verify_and_login')}}</button>
                                        <a href="{{ url('admin/logout') }}" class="ga-btn ga-btn--ghost">{{getTranslateByKey('main_ga_abandon_login')}}</a>
                                    </div>
                                </form>
                            </div>
                        @endif

                        @if($mode === 'manage' && $hasBinding)
                            <div class="ga-block ga-block--full">
                                <h3 class="ga-block__title">{{getTranslateByKey('main_ga_disable_title')}}</h3>
                                <div class="ga-block__desc">{{getTranslateByKey('main_ga_disable_desc')}}</div>
                                <form method="post" action="{{ $disableAction }}">
                                    {{ csrf_field() }}
                                    <div class="ga-field" style="margin-top:18px;">
                                        <label class="ga-field__label">{{getTranslateByKey('main_ga_current_code')}}</label>
                                        <input class="ga-input" type="text" name="google_authenticator_code" maxlength="6" placeholder="{{getTranslateByKey('main_ga_enter_current_code')}}" autocomplete="one-time-code">
                                        <div class="ga-field__help">{{getTranslateByKey('main_ga_disable_help')}}</div>
                                    </div>
                                    <div class="ga-actions">
                                        <button type="submit" class="ga-btn ga-btn--danger">{{getTranslateByKey('main_ga_disable_btn')}}</button>
                                        <a href="{{ url('admin/system/secure/secureConfig') }}" class="ga-btn ga-btn--ghost">{{getTranslateByKey('main_ga_back_security')}}</a>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                <div class="ga-side">
                    <h3 class="ga-side__title">{{getTranslateByKey('main_ga_steps_title')}}</h3>
                    <div class="ga-side__steps">
                        <div class="ga-step">
                            <div class="ga-step__index">1</div>
                            <div class="ga-step__body">
                                <div class="ga-step__title">{{getTranslateByKey('main_ga_step1_title')}}</div>
                                {{getTranslateByKey('main_ga_step1_desc')}}
                            </div>
                        </div>
                        <div class="ga-step">
                            <div class="ga-step__index">2</div>
                            <div class="ga-step__body">
                                <div class="ga-step__title">{{getTranslateByKey('main_ga_step2_title')}}</div>
                                {{getTranslateByKey('main_ga_step2_desc')}}
                            </div>
                        </div>
                        <div class="ga-step">
                            <div class="ga-step__index">3</div>
                            <div class="ga-step__body">
                                <div class="ga-step__title">{{getTranslateByKey('main_ga_step3_title')}}</div>
                                {{getTranslateByKey('main_ga_step3_desc')}}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ga-side">
                    <h3 class="ga-side__title">{{getTranslateByKey('main_ga_summary_title')}}</h3>
                    <div class="ga-side__meta"><strong>{{getTranslateByKey('main_ga_label_account')}}</strong>{{ $adminUser['username'] ?? ('UID:' . ($adminUser['uid'] ?? 0)) }}</div>
                    <div class="ga-side__meta"><strong>{{getTranslateByKey('main_ga_label_mode')}}</strong>{{ $modeLabel }}</div>
                    <div class="ga-side__meta"><strong>{{getTranslateByKey('main_ga_label_policy')}}</strong>{{ $policyLabel }}</div>
                    <div class="ga-side__meta"><strong>{{getTranslateByKey('main_ga_label_status')}}</strong>{{ $hasBinding ? getTranslateByKey('main_ga_bound') : getTranslateByKey('main_ga_unbound') }}</div>
                    <div class="ga-side__meta"><strong>{{getTranslateByKey('main_ga_label_note')}}</strong>{{ $isPendingLogin ? getTranslateByKey('main_ga_note_login_flow') : getTranslateByKey('main_ga_note_account_only') }}</div>
                </div>

                <div class="ga-side">
                    <h3 class="ga-side__title">{{getTranslateByKey('main_ga_notice_title')}}</h3>
                    <ol class="ga-list">
                        <li>{{getTranslateByKey('main_ga_notice_1')}}</li>
                        <li>{{getTranslateByKey('main_ga_notice_2')}}</li>
                        <li>{{getTranslateByKey('main_ga_notice_3')}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ commonAsset('lib/jquery/admin/jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ ADMIN_ASSET }}other/jqueryToast/js/toast.script.js"></script>
<script>
    var toastMsg = '';
    var toastType = '';

    @if(session('statusmsg'))
        toastType = 'success';
        toastMsg = @json(session('statusmsg'));
    @endif

    @if ($errors->any())
        toastType = 'error';
        @foreach ($errors->all() as $error)
            toastMsg = @json($error);
        @endforeach
    @endif

    if (toastMsg) {
        $.Toast(@json(getTranslateByKey('common_toast_title')), toastMsg, toastType, {
            appendTo: "body",
            stack: true,
            position_class: "toast-bottom-right",
            fullscreen: false,
            width: 260,
            spacing: 20,
            timeout: 2200,
            has_close_btn: false,
            has_icon: false,
            sticky: false,
            border_radius: 6,
            has_progress: true,
            rtl: false
        });
    }
</script>
</body>
</html>
