@include(moduleAdminTemplate($moduleName)."public.header")

<body>

<!--                        Topbar End                              -->
<!-- ============================================================== -->


<!-- ============================================================== -->
<!-- 						Navigation Start 						-->
<!-- ============================================================== -->

@include(moduleAdminTemplate($moduleName)."public.nav")
<!-- ============================================================== -->
<!-- 						Navigation End	 						-->
<!-- ============================================================== -->

<!-- Page container -->
<div class="page-container">

    <!-- Page content -->
    <div class="page-content">

        <!-- Main sidebar -->
        <div class="sidebar sidebar-main">
            <div class="sidebar-content">
                @include(moduleAdminTemplate($moduleName)."public.left")
            </div>
        </div>
        <!-- /main sidebar -->


        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Page header -->
            <div class="page-header">

                @include(moduleAdminTemplate($moduleName)."public.page",['breadcrumb'=>[getTranslateByKey('auth_system_settings'), getTranslateByKey('menu_list')]])

                <!-- Content area -->
                <div class="content" style="margin-top: 1rem;">


                    <!-- Bordered striped table -->
                    <div class="panel panel-flat">

                        <div class="table-responsive panel panel-default">
                            <div class="panel-heading">

                                <a class="label bg-info pull-right m-t-xs {{permissions('menu/menuAdd')}}"
                                   href="{{moduleAdminJump($moduleName,'menu/menuAdd')}}">
                                    {{ getTranslateByKey('auth_add') }}
                                </a>
                                {{ getTranslateByKey('auth_list') }}
                            </div>
                            <table class="table m-b-none col-sm-12">
                                <thead>
                                <tr>
                                    <th>{{ getTranslateByKey('auth_title') }}</th>
                                    <th>{{ getTranslateByKey('auth_belong_module') }}</th>
                                    <th>{{ getTranslateByKey('auth_controller') }}</th>
                                    <th>{{ getTranslateByKey('auth_action') }}</th>
                                    <th>{{ getTranslateByKey('auth_url_path') }}</th>
                                    <th>{{ getTranslateByKey('auth_is_hide') }}</th>
                                    <th>{{ getTranslateByKey('auth_sort') }}</th>
                                    <th>{{ getTranslateByKey('auth_time') }}</th>
                                    <th>{{ getTranslateByKey('operation') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($pageData['datas'] as $d)
                                    <tr style="background-color: #e2e2e2">
                                        <td>{{$d['title']}}</td>
                                        <td>{{$d['module']}}</td>
                                        <td>{{$d['controller']}}</td>
                                        <td>{{$d['action']}}</td>
                                        <td>{{$d['url']}}</td>
                                        <td>
                                            @if($d['is_hide']==1)
                                                <span class="text-danger">
                                                {{ getTranslateByKey('auth_hidden') }}
                                            </span>
                                            @else
                                                <span class="text-success">
                                                {{ getTranslateByKey('auth_visible') }}
                                            </span>
                                            @endif
                                        </td>
                                        <td>{{$d['orders']}}</td>
                                        <td>{{date('Y-m-d H:i:s',$d['create_at'])}}</td>
                                        <td>
                                            <a href="{{moduleAdminJump($moduleName,'menu/menuEdit?id='.$d['id'])}}"
                                               class="{{permissions('menu/menuEdit')}}">
                                                <button type="button" class="h-button-edit btn btn-info btn-xs">
                                                    {{ getTranslateByKey('common_edit') }}
                                                </button>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-danger btn-xs {{permissions('menu/menuDelete')}}"
                                                    onclick="_confirm('{{moduleAdminJump($moduleName,'menu/menuDelete')}}',{
                                                            '_method':'DELETE','_token':'{{csrf_token()}}','id':'{{$d['id']}}'
                                                            },'{{ getTranslateByKey('common_sure_to_delete') }}')">
                                                {{ getTranslateByKey('common_delete') }}
                                            </button>
                                            <button type="button" class="btn btn-primary btn-xs"
                                                    onclick="toggle('child{{$d['id']}}')">
                                                {{ getTranslateByKey('auth_expand_shrink') }}
                                            </button>
                                        </td>
                                    </tr>
                                    @foreach($d['child'] as $p)
                                        <tr class="child{{$d['id']}}">
                                            <td>└─ {{$p['title']}}</td>
                                            <td>{{$p['module']}}</td>
                                            <td>{{$p['controller']}}</td>
                                            <td>{{$p['action']}}</td>
                                            <td>{{$p['url']}}</td>
                                            <td>
                                                @if($p['is_hide']==1)
                                                    <span class="text-danger">
                                                {{ getTranslateByKey('auth_hidden') }}
                                            </span>
                                                @else
                                                    <span class="text-success">
                                                {{ getTranslateByKey('auth_visible') }}
                                            </span>
                                                @endif
                                            </td>
                                            <td>{{$p['orders']}}</td>
                                            <td>{{date('Y-m-d H:i:s',$p['create_at'])}}</td>
                                            <td>
                                                <a href="{{moduleAdminJump($moduleName,'menu/menuEdit?id='.$p['id'])}}"
                                                   class="{{permissions('menu/menuEdit')}}">
                                                    <button type="button" class="h-button-edit btn btn-info btn-xs">
                                                        {{ getTranslateByKey('common_edit') }}
                                                    </button>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-danger btn-xs {{permissions('menu/menuDelete')}}"
                                                        onclick="_confirm('{{moduleAdminJump($moduleName,'menu/menuDelete')}}',{
                                                                    '_method':'DELETE','_token':'{{csrf_token()}}','id':'{{$p['id']}}'
                                                                    },'{{ getTranslateByKey('common_sure_to_delete') }}')">
                                                    {{ getTranslateByKey('common_delete') }}
                                                </button>
                                            </td>
                                        </tr>
                                        @foreach($p['child'] as $p2)
                                            <tr class="child{{$p2['id']}}">
                                                <td>└─└─ {{$p2['title']}}</td>
                                                <td>{{$p2['module']}}</td>
                                                <td>{{$p2['controller']}}</td>
                                                <td>{{$p2['action']}}</td>
                                                <td>{{$p2['url']}}</td>
                                                <td>
                                                    @if($p2['is_hide']==1)
                                                        <span class="text-danger">
                                                {{ getTranslateByKey('auth_hidden') }}
                                            </span>
                                                    @else
                                                        <span class="text-success">
                                                {{ getTranslateByKey('auth_visible') }}
                                            </span>
                                                    @endif
                                                </td>
                                                <td>{{$p2['orders']}}</td>
                                                <td>{{date('Y-m-d H:i:s',$p2['create_at'])}}</td>
                                                <td>
                                                    <a href="{{moduleAdminJump($moduleName,'menu/menuEdit?id='.$p2['id'])}}"
                                                       class="{{permissions('menu/menuEdit')}}">
                                                        <button type="button" class="h-button-edit btn btn-info btn-xs">
                                                            {{ getTranslateByKey('common_edit') }}
                                                        </button>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-danger btn-xs {{permissions('menu/menuDelete')}}"
                                                            onclick="_confirm('{{moduleAdminJump($moduleName,'menu/menuDelete')}}',{
                                                                    '_method':'DELETE','_token':'{{csrf_token()}}','id':'{{$p2['id']}}'
                                                                    },'{{ getTranslateByKey('common_sure_to_delete') }}')">
                                                        {{ getTranslateByKey('common_delete') }}
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="20">
                                            {{ getTranslateByKey('no_data') }}
                                        </td>
                                    </tr>
                                @endforelse


                                </tbody>
                            </table>

                        </div>
                    </div>
                    <!-- /bordered striped table -->


                    <!-- Footer -->
                    @include(moduleAdminTemplate($moduleName)."public.footer")
                    <!-- /footer -->

                </div>
                <!-- /content area -->

            </div>
            <!-- /main content -->

        </div>
        <!-- /page content -->

    </div>

    <!-- 						Content End		 						-->
    <!-- ============================================================== -->
    @include(moduleAdminTemplate($moduleName)."public.js")
    <script>
        function toggle(_class) {
            $("." + _class).slideToggle();
        }
    </script>
</body>
</html>
