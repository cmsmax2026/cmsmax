
<!-- Jquery UI -->
<script src="{{commonAsset('lib/jquery-ui/jquery-ui.min.js')}}"></script>
<script src="{{ADMIN_ASSET}}js/jquery.ui.custom.js"></script>

<!--Sweet Alerts-->
<script type="text/javascript" src="{{ADMIN_ASSET}}js/jquery-confirm.min.js"></script>

<script type="text/javascript" src="{{ADMIN_ASSET}}other/DialogJS/javascript/zepto.min.js"></script>
<script type="text/javascript" src="{{ADMIN_ASSET}}other/DialogJS/javascript/dialog.min.js"></script>

<script type="text/javascript" src="{{ADMIN_ASSET}}other/jqueryToast/js/toast.script.js"></script>

<script type="text/javascript" src="{{commonAsset('lib/layer/layer/layer.js')}}"></script>
<!---->
@foreach($load as $value)
    @if($value)
        <script src="{{ADMIN_ASSET}}js/{{$value}}.js"></script>
    @endif
@endforeach
@include('admin.public.cmsjs')
<script>

    var toastMsg;
    var toastType
    var interval_id;

    @if(session('errormsg'))
        toastMsg = "{{session('errormsg')}}";
        toastType = 'error';
    @elseif(session('successmsg'))
        toastMsg = "{{session('successmsg')}}";
        toastType = 'success';
    @endif

    if (toastMsg) {
        $.Toast(@json(getTranslateByKey('common_toast_title')), toastMsg, toastType, {
            // append to body
            appendTo: "body",
            // is stackable?
            stack: true,
            // 'toast-top-left'
            // 'toast-top-right'
            // 'toast-top-center'
            // 'toast-bottom-left'
            // 'toast-bottom-right'
            // 'toast-bottom-center'
            position_class: "toast-bottom-right",
            // true = snackbar
            fullscreen: false,
            // width
            width: 250,
            // space between toasts
            spacing: 20,
            // in milliseconds
            timeout: 2000,
            // has close button
            has_close_btn: false,
            // has icon
            has_icon: false,
            // is sticky
            sticky: false,
            // border radius in pixels
            border_radius: 6,
            // has progress bar
            has_progress: true,
            // RTL support
            rtl: false
        });
        toastMsg = null

    }

    @if(session('datapolicy_detail'))
        // 手动处理数据策略的详细操作步骤：toast 只显示简短提示，完整步骤用弹窗展示
        $.confirm({
            title: '{{getTranslateByKey('main_datapolicy_manual_title')}}',
            content: @json(session('datapolicy_detail')),
            type: 'orange',
            boxWidth: '520px',
            useBootstrap: false,
            theme: 'modern',
            buttons: {
                '{{getTranslateByKey('main_got_it')}}': {
                    btnClass: 'btn-orange',
                }
            }
        });
    @endif

    // 列表里「?」图标：点击弹出该包在某生命周期动作下的数据策略说明（删除/禁用/卸载通用）
    $(document).on('click', '[data-policy-help]', function () {
        var content = $(this).data('policyHelp');
        if (!content) {
            return;
        }
        $.confirm({
            title: '{{getTranslateByKey('main_datapolicy_title')}}',
            content: '<div class="text-left" style="text-align:left">' + content + '</div>',
            type: 'orange',
            boxWidth: '520px',
            useBootstrap: false,
            theme: 'modern',
            buttons: {
                '{{getTranslateByKey('main_got_it')}}': {
                    btnClass: 'btn-orange',
                }
            }
        });
    });
</script>





