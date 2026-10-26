@include("admin.public.header")
<body class="horizontal">
@include("admin.public.topbar")
@include("admin.public.nav")
<style type="text/css">
    .display-none {
        display: none;
    }
    .onlineModuleList {
        cursor: pointer;
    }
    .cursor{
        cursor: pointer;
    }
    .mokuai .row div.col-md-3 {
        height: 340px;
        margin: 2rem 0;
    }
    .box {
        box-shadow: 0 0 8px #ccc;
        border-radius: 15px;
    }
    .box img {
        width: 101%;
        height: 215px;
    }
    .clearfix:after {
        visibility: hidden;
        clear: both;
        display: block;
        content: ".";
        height: 0;
    }
    .clearfix {
        *zoom: 1;
    }
    .plugin-config-notice {
        margin-bottom: 18px;
        padding: 12px 16px;
        border: 1px solid #f3d19e;
        border-left: 4px solid #e6a23c;
        border-radius: 10px;
        background: #fffaf3;
        font-size: 13px;
        color: #5b4a2f;
        line-height: 1.7;
    }
    .plugin-config-notice strong {
        color: #a16207;
    }
    .plugin-config-path {
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px dashed #e7edf5;
        font-size: 12px;
        color: #8a94a6;
        word-break: break-all;
    }
    .plugin-config-empty {
        padding: 28px 16px;
        border: 1px dashed #d7dde5;
        border-radius: 10px;
        background: #fbfcfe;
        color: #8a94a6;
        text-align: center;
        font-size: 13px;
    }
</style>
<div class="row page-header">
    <div class="col-lg-6 align-self-center ">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url('admin/index')}}">{{getTranslateByKey("common_home_page")}}</a></li>
            <li class="breadcrumb-item"><a href="{{url('admin/plugin')}}">{{getTranslateByKey("plugin_management")}}</a></li>
            <li class="breadcrumb-item active">{{getTranslateByKey("plugin_configuration")}}</li>
        </ol>
    </div>
</div>

<section class="container-fluid pl-5 pr-5 ">

    <div class="row pb-5">
        <div class="col-12 bg-light" style="min-height: calc(100vh - 250px)">
            <div class="">
                <div class="card-header card-default">
                    <div class="row">
                        <div class="col-md-12 col-12">
                            {{$pageData['subtitle']}}
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <div class="plugin-config-notice">
                        {!! getTranslateByKey('main_plugin_config_notice_1') !!}
                        <br>
                        {!! getTranslateByKey('main_plugin_config_notice_2') !!}
                        <br>
                        {!! getTranslateByKey('main_plugin_config_notice_3') !!}
                    </div>

                    @if(!empty($pageData['configEmpty']))
                        <div class="plugin-config-empty">
                            {{getTranslateByKey('main_plugin_config_empty_1')}}
                            <br>
                            {{getTranslateByKey('main_plugin_config_empty_2')}}
                        </div>
                    @endif

                    <form class="w-100" action="{{$pageData['formaction']}}"
                          method="{{$pageData['method']}}"
                          id="{{$pageData['formid']}}"
                          enctype="multipart/form-data">
                        @foreach($pageData['fields'] as $f)
                            @include(moduleAdminTemplate('formtools')."formtooltemplates.".$f['formtype'],compact( 'f'))
                        @endforeach
                        <div class="form-group">
                            <label class="col-lg-1 control-label"></label>
                            <div class="col-lg-11">
                                <button type="submit" class="btn btn-sm btn-info" id="post_button" style="cursor: pointer;">
                                    {{$pageData['actionName']}}
                                </button>
                                <a href="{{url("admin/plugin")}}" type="button" class="btn btn-sm btn-danger" >
                                    {{$pageData['backName']}}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('admin.public.footer')

</section>

@include('admin.public.js',['load'=> ["custom"]])
<script type="text/javascript">
    $(function () {
        var $form = $('#{{$pageData['formid']}}');
        $('#post_button').on('click', function (event) {
            if (typeof adminConfirm !== 'function') {
                return true;
            }

            event.preventDefault();
            adminConfirm({
                title: '{{getTranslateByKey("common_tip")}}',
                content: '{{getTranslateByKey('main_plugin_config_save_confirm')}}',
                onConfirm: function () {
                    if ($form[0]) {
                        $form[0].submit();
                    }
                }
            });
            return false;
        });
    });
</script>
</body>
</html>
