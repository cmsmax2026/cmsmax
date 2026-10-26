
<!-- Theme JS files -->
<script type="text/javascript" src="{{commonAsset('lib/jquery-ui/jquery-ui.min.js')}}"></script>
<script type="text/javascript"
        src="{{asset("assets/module")}}/js/plugins/visualization/d3/d3.min.js"></script>
<script type="text/javascript"
        src="{{asset("assets/module")}}/js/plugins/visualization/d3/d3_tooltip.js"></script>
<script type="text/javascript"
        src="{{asset("assets/module")}}/js/plugins/forms/styling/switchery.min.js"></script>
<script type="text/javascript"
        src="{{asset("assets/module")}}/js/plugins/forms/styling/uniform.min.js"></script>
<script type="text/javascript" src="{{asset("assets/module")}}/js/pages/form_layouts.js"></script>
<script type="text/javascript"
        src="{{asset("assets/module")}}/js/plugins/forms/selects/bootstrap_multiselect.js"></script>
<script type="text/javascript" src="{{commonAsset('lib/moment/moment.min.js')}}"></script>
<script type="text/javascript"
        src="{{asset("assets/module")}}/js/plugins/pickers/daterangepicker.js"></script>
<script type="text/javascript" src="{{asset("assets/module")}}/js/plugins/forms/selects/select2.min.js"></script>
<script type="text/javascript" src="{{asset("assets/module")}}/js/core/app.js"></script>

<!-- /theme JS files -->

<!--   弹窗 -->
<script type="text/javascript" src="{{commonAsset('lib/layer/layer/layer.js')}}"></script>
<!--   弹窗end -->
<script>
    var alert_title = '{{ getTranslateByKey('confirm_operation_prompt') }}';
    var alert_yes = '{{ getTranslateByKey('confirm_action_label') }}';
    var alert_no = '{{ getTranslateByKey('cancel_action_label') }}';
    var alert_info = '{{ getTranslateByKey('info_title') }}';
    var choose_file = '{{ getTranslateByKey('choose_file_label') }}';
    var clear_url = "";
    var alert_data = {};

    @if(session('pageDataMsg'))
    layer.msg("{{session('pageDataMsg')}}", {
        area: ['100%', '50px']
        , offset: 'rt' // 具体配置参考 offset 参数
        , shade: 0 // 不显示遮罩
    });
    $('.layui-layer-msg').css({
        'backgroundColor': '{{session('pageDataStatus')==200?'#4caf50':'#f44336'}}'
    });
    {{session(['pageDataMsg'=>null])}}
    @endif

    if (typeof window.formtoolsConfirmDelete !== 'function') {
        window.formtoolsConfirmDelete = function (url, title, description) {
            layer.confirm(description || '{{ getTranslateByKey('ft_delete_irrecoverable_confirm') }}', {
                title: title || '{{ getTranslateByKey('ft_delete_confirm_title') }}',
                btn: ['{{ getTranslateByKey('ft_confirm_delete') }}', '{{ getTranslateByKey('cancel_action_label') }}']
            }, function () {
                window.location.href = url;
            });
        };
    }

    if (typeof window.del !== 'function') {
        window.del = function (url) {
            window.formtoolsConfirmDelete(url, '{{ getTranslateByKey('ft_delete_confirm_title') }}', '{{ getTranslateByKey('ft_delete_irrecoverable_confirm') }}');
            return false;
        };
    }
</script>
<script type="text/javascript" src="{{asset("assets/module")}}/js/h-app.js"></script>
