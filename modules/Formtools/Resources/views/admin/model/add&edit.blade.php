@include(moduleAdminTemplate($moduleName)."public.header")
@php($moduleName = strtolower((string) $moduleName))
<!-- ============================================================== -->
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

    @include(moduleAdminTemplate($moduleName)."public.left")


    <!-- Main content -->
        <div class="content-wrapper">

            <!-- Content area -->
            <div class="content">
                @include(moduleAdminTemplate($moduleName)."public.crumb",['breadcrumb'=>[$pageData['title'],$pageData['subtitle']]])
                <div class="alert alert-info alert-styled-left">
                    <span>{{ getTranslateByKey('ft_me_seo_note') }}</span>
                    @if(!empty($pageData['access_identification']))
                        <br>
                        <span>{{ getTranslateByKey('ft_me_home_list') }}</span><code>{{ model_list_url($pageData['access_identification']) }}</code>
                        <span style="margin-left: 12px;">{{ getTranslateByKey('ft_me_home_detail') }}</span><code>{{ model_detail_url($pageData['access_identification'], '{id}') }}</code>
                    @endif
                </div>
                <div class="panel panel-flat">
                    <div class="panel-heading">

                        <form class="form-horizontal" action="{{url("admin/".strtolower($moduleName)."/model?"."action=Submit&moduleName={$pageData['moduleName']}&model=".$pageData['model']."&page=".$pageData['page'])}}" method="post" id="post_form" enctype="multipart/form-data">
                            {{csrf_field()}}
                            <fieldset class="content-group">
                                <legend class="text-bold">{{$pageData['title']}}</legend>
                                @foreach($pageData['fields'] as $f)
                                    @include(moduleAdminTemplate("formtools")."formtooltemplates.".$f['formtype'],compact( 'f'))
                                @endforeach

                                <div class="form-group row">
                                    <label class="col-lg-1 control-label">
                                        {{ getTranslateByKey('ft_me_status') }}
                                    </label>
                                    <div class="col-lg-11">
                                        <label class="radio-inline">
                                            <input type="radio" class="styled"
                                                   name="status"
                                                   value=1
                                                   @if($pageData['status']==1) checked @endif
                                            > {{ getTranslateByKey('ft_api_pass') }}
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" class="styled"
                                                   name="status"
                                                   value=0
                                                   @if($pageData['status']==0) checked @endif
                                            > {{ getTranslateByKey('ft_api_not_pass') }}
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" class="styled"
                                                   name="status"
                                                   value=2
                                                   @if($pageData['status']==2) checked @endif
                                            > {{ getTranslateByKey('ft_api_offline') }}
                                        </label>
                                        <span class="help-block">{{ getTranslateByKey('ft_me_status') }}</span>
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-1 control-label">
                                        {{ getTranslateByKey('ft_me_remark') }}
                                    </label>
                                    <div class="col-lg-11">
                                        <input type="text" id="remark"
                                               name="remark"
                                               class="form-control"
                                               placeholder="{{ getTranslateByKey('ft_me_remark_placeholder') }}"
                                               value="{{$pageData['remark']}}"
                                        >
                                        <span class="help-block">{{ getTranslateByKey('ft_me_remark') }}</span>
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-1 control-label">
                                        {{ getTranslateByKey('ft_me_seo_title') }}
                                    </label>
                                    <div class="col-lg-11">
                                        <input type="text" id="seo_title"
                                               name="seo_title"
                                               class="form-control"
                                               placeholder="{{ getTranslateByKey('ft_me_seo_title_placeholder') }}"
                                               value="{{$pageData['seo_title']}}"
                                        >

                                        <span class="help-block">{{ getTranslateByKey('ft_me_seo_title_help') }}</span>

                                    </div>

                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-1 control-label">
                                        {{ getTranslateByKey('ft_me_seo_keywords') }}
                                    </label>
                                    <div class="col-lg-11">
                                        <input type="text" id="seo_keywords"
                                               name="seo_keywords"
                                               class="form-control"
                                               placeholder="{{ getTranslateByKey('ft_me_seo_keywords_placeholder') }}"
                                               value="{{$pageData['seo_keywords']}}"
                                        >

                                        <span class="help-block">{{ getTranslateByKey('ft_me_seo_fallback') }}</span>

                                    </div>

                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-1 control-label">
                                        {{ getTranslateByKey('ft_me_seo_description') }}
                                    </label>
                                    <div class="col-lg-11">
                                        <textarea name="seo_description"
                                                  id="seo_description" cols="30" rows="4"
                                                  placeholder="{{ getTranslateByKey('ft_me_seo_description_placeholder') }}"
                                                  class="form-control">{{$pageData['seo_description']}}</textarea>
                                        <span class="help-block">{{ getTranslateByKey('ft_me_seo_fallback') }}</span>
                                    </div>

                                </div>

                                <div class="form-group">
                                    <label class="col-lg-1 control-label"></label>
                                    <div class="col-lg-11">
                                        <button type="submit" class="btn btn-sm btn-info" id="post_button">
                                            {{ getTranslateByKey('common_submit') }}
                                        </button>
                                        @if(!empty($pageData['access_identification']))
                                            <a href="{{ model_list_url($pageData['access_identification']) }}" target="_blank" type="button" class="btn btn-sm btn-default">
                                                {{ getTranslateByKey('ft_home_preview') }}
                                            </a>
                                        @endif
                                        <a href="{{url("admin/".$moduleName."/model?moduleName={$pageData['moduleName']}&action=List&model=".$pageData['model']."&page=".$pageData['page'])}}" type="button" class="btn btn-sm btn-danger" >
                                            {{ getTranslateByKey('common_back') }}
                                        </a>
                                    </div>
                                </div>
                            </fieldset>

                        </form>
                    </div>
                </div>


                @include(moduleAdminTemplate($moduleName)."public.footer")


            </div>
            <!-- /content area -->

        </div>
        <!-- /main content -->

    </div>
    <!-- /page content -->

</div>
<!-- /page container -->

<!-- 						Content End		 						-->
<!-- ============================================================== -->
@include(moduleAdminTemplate($moduleName)."public.js")

{{-- 必须放在最后 --}}
<script type="text/javascript"
        src="{{asset("assets/module")}}/js/plugins/forms/selects/select2.min.js"></script>
<script type="text/javascript" src="{{asset("assets/module")}}/js/pages/form_select2.js"></script>
</body>
</html>

