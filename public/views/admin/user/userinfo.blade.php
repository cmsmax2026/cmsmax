@include("admin.public.header")

<body class="horizontal">

<!-- ============================================================== -->
<!-- 						Topbar Start 							-->
<!-- ============================================================== -->
@include("admin.public.topbar")
<!-- ============================================================== -->
<!--                        Topbar End                              -->
<!-- ============================================================== -->

<!-- ============================================================== -->
<!-- 						Navigation Start 						-->
<!-- ============================================================== -->
@include("admin.public.nav")
<!-- ============================================================== -->
<!-- 						Navigation End	 						-->
<!-- ============================================================== -->


<!-- ============================================================== -->
<!-- 						Content Start	 						-->
<!-- ============================================================== -->

<div class="row page-header">
    <div class="col-lg-6 align-self-center ">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">{{getTranslateByKey("common_home_page")}}</a></li>
            <li class="breadcrumb-item"><a href="#">{{getTranslateByKey("user_manage")}}</a></li>
            <li class="breadcrumb-item active">{{getTranslateByKey("user_details")}}</li>
        </ol>
    </div>
</div>

<style>
    .myinfo {
        padding-left: 28px;
        padding-right: 28px;
    }

    .myinfo .card-body {
        padding: 22px;
    }

    .myinfo__title {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
    }

    .myinfo__desc {
        margin: 10px 0 0 0;
        color: #6c757d;
        font-size: 13px;
        line-height: 1.6;
    }

    .myinfo__profile {
        display: flex;
        gap: 14px;
        align-items: center;
    }

    .myinfo__avatar {
        width: 64px;
        height: 64px;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,.08);
        background: #f8f9fa;
        flex: 0 0 auto;
    }

    .myinfo__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .myinfo__name {
        min-width: 0;
    }

    .myinfo__username {
        font-size: 16px;
        font-weight: 600;
        color: #212529;
        line-height: 1.2;
        word-break: break-all;
    }

    .myinfo__nickname {
        margin-top: 4px;
        font-size: 13px;
        color: #6c757d;
        word-break: break-all;
    }

    .myinfo__badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    .myinfo__badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        border: 1px solid rgba(0,0,0,.08);
        background: #fff;
        color: #495057;
    }

    .myinfo__badge--success {
        color: #0f5132;
        background: rgba(25,135,84,.08);
        border-color: rgba(25,135,84,.20);
    }

    .myinfo__badge--danger {
        color: #842029;
        background: rgba(220,53,69,.08);
        border-color: rgba(220,53,69,.20);
    }

    .myinfo__badge--info {
        color: #055160;
        background: rgba(13,110,253,.08);
        border-color: rgba(13,110,253,.20);
    }

    .myinfo__meta {
        margin-top: 16px;
        border-top: 1px solid rgba(0,0,0,.06);
        padding-top: 14px;
        display: grid;
        gap: 10px;
    }

    .myinfo__meta-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 13px;
    }

    .myinfo__meta-label {
        color: #6c757d;
        flex: 0 0 auto;
    }

    .myinfo__meta-value {
        color: #212529;
        text-align: right;
        word-break: break-all;
    }

    .myinfo__section-title {
        font-size: 14px;
        font-weight: 600;
        color: #212529;
        margin: 16px 0 10px 0;
    }

    .myinfo__divider {
        height: 1px;
        background: rgba(0,0,0,.06);
        margin: 16px 0;
    }

    .myinfo .fileinput-preview {
        width: 140px !important;
        height: 140px !important;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,.08);
        background: #f8f9fa;
    }

    .myinfo .fileinput-preview img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        padding: 8px;
        background: #fff;
    }

    @media (max-width: 767px) {
        .myinfo {
            padding-left: 16px;
            padding-right: 16px;
        }

        .myinfo .card-body {
            padding: 18px;
        }
    }
</style>

<section class="main-content myinfo">
    <div class="row">
        <div class="col-lg-4 margin-b-20">
            <div class="card">
                <div class="card-body">
                    <div class="myinfo__profile">
                        <div class="myinfo__avatar">
                            <img src="{{GetUrlByPath($member['avatar'])}}" alt="">
                        </div>
                        <div class="myinfo__name">
                            <div class="myinfo__username">{{$member['username'] ?: getTranslateByKey('main_not_set_account')}}</div>
                            <div class="myinfo__nickname">{{$member['nickname'] ?: getTranslateByKey('main_not_set_nickname')}}</div>
                        </div>
                    </div>

                    <div class="myinfo__badges">
                        @if((int) $member['status'] === 1)
                            <span class="myinfo__badge myinfo__badge--success">{{getTranslateByKey("common_enable")}}</span>
                        @else
                            <span class="myinfo__badge myinfo__badge--danger">{{getTranslateByKey("common_disable")}}</span>
                        @endif
                        @if((int) $member['email_active'] === 1)
                            <span class="myinfo__badge myinfo__badge--info">{{getTranslateByKey('main_email_verified')}}</span>
                        @else
                            <span class="myinfo__badge">{{getTranslateByKey('main_email_not_verified')}}</span>
                        @endif
                        @if((int) $member['phone_active'] === 1)
                            <span class="myinfo__badge myinfo__badge--info">{{getTranslateByKey('main_phone_verified')}}</span>
                        @else
                            <span class="myinfo__badge">{{getTranslateByKey('main_phone_not_verified')}}</span>
                        @endif
                    </div>

                    <div class="myinfo__meta">
                        <div class="myinfo__meta-row">
                            <span class="myinfo__meta-label">UID</span>
                            <span class="myinfo__meta-value">{{$member['uid']}}</span>
                        </div>
                        <div class="myinfo__meta-row">
                            <span class="myinfo__meta-label">{{getTranslateByKey("contact_phone")}}</span>
                            <span class="myinfo__meta-value">{{$member['phone'] ?: getTranslateByKey('main_not_set')}}</span>
                        </div>
                        <div class="myinfo__meta-row">
                            <span class="myinfo__meta-label">{{getTranslateByKey("common_email")}}</span>
                            <span class="myinfo__meta-value">{{$member['email'] ?: getTranslateByKey('main_not_set')}}</span>
                        </div>
                        <div class="myinfo__meta-row">
                            <span class="myinfo__meta-label">{{getTranslateByKey("sex")}}</span>
                            <span class="myinfo__meta-value">{{$member['male'] ?: getTranslateByKey('main_not_set')}}</span>
                        </div>
                        <div class="myinfo__meta-row">
                            <span class="myinfo__meta-label">{{getTranslateByKey("birthday")}}</span>
                            <span class="myinfo__meta-value">{{$member['birthday'] ?: getTranslateByKey('main_not_set')}}</span>
                        </div>
                    </div>

                    <div class="myinfo__divider"></div>

                    <div class="myinfo__desc">
                        {{getTranslateByKey("user_if_empty_not_change")}}：{{getTranslateByKey("common_login_password")}}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 margin-b-20">
            <div class="card">
                <div class="card-body">
                    <h4 class="myinfo__title">{{getTranslateByKey('main_my_info')}}</h4>
                    <p class="myinfo__desc">{{getTranslateByKey('main_my_info_desc')}}</p>

                    <form method="post" action="" id="userinfo_form" enctype="multipart/form-data" style="margin-top: 12px;">
                        {{csrf_field()}}
                        <input name="uid" value="{{$member['uid']}}" type="hidden" />

                        <div class="myinfo__section-title">{{getTranslateByKey('main_avatar')}}</div>
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label>{{getTranslateByKey("user_avater")}}</label>
                            <div class="fileinput-new" data-provides="fileinput">
                                <div class="fileinput-preview" data-trigger="fileinput">
                                    <img src="{{GetUrlByPath($member['avatar'])}}" alt="">
                                </div>
                                <div style="margin-top: 10px;">
                                    <span class="btn btn-primary btn-file">
                                        <span class="fileinput-new">{{getTranslateByKey("common_select")}}</span>
                                        <span class="fileinput-exists">{{getTranslateByKey("common_change")}}</span>
                                        <input type="file" id="image" name="avatar">
                                    </span>
                                    <a href="#" class="btn btn-danger fileinput-exists" data-dismiss="fileinput">{{getTranslateByKey("common_delete")}}</a>
                                </div>
                            </div>
                        </div>

                        <div class="myinfo__divider"></div>

                        <div class="myinfo__section-title">{{getTranslateByKey('main_basic_info')}}</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{getTranslateByKey("common_username")}}</label>
                                    <input type="text" name="username" value="{{$member['username']}}" class="form-control form-control-rounded">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{getTranslateByKey("common_nickname")}}</label>
                                    <input type="text" name="nickname" value="{{$member['nickname']}}" class="form-control form-control-rounded">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{getTranslateByKey("contact_phone")}}</label>
                                    <input type="text" name="phone" value="{{$member['phone']}}" class="form-control form-control-rounded">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{getTranslateByKey("common_email")}}</label>
                                    <input type="text" name="email" value="{{$member['email']}}" class="form-control form-control-rounded">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{getTranslateByKey("common_phone_active")}}</label>
                                    <div class="form-inline">
                                        <div class="radio radio-inline radio-success">
                                            <input id="phone_active" type="radio" name="phone_active" value="0" @if($member['phone_active']==0) checked @endif >
                                            <label for="phone_active"> {{getTranslateByKey("common_not_active")}} </label>
                                        </div>
                                        <div class="radio radio-inline radio-success">
                                            <input id="phone_active1" type="radio" name="phone_active" value="1" @if($member['phone_active']==1) checked @endif >
                                            <label for="phone_active1"> {{getTranslateByKey("common_active")}} </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{getTranslateByKey("common_email_active")}}</label>
                                    <div class="form-inline">
                                        <div class="radio radio-inline radio-success">
                                            <input id="email_active" type="radio" name="email_active" value="0" @if($member['email_active']==0) checked @endif >
                                            <label for="email_active"> {{getTranslateByKey("common_not_active")}} </label>
                                        </div>
                                        <div class="radio radio-inline radio-success">
                                            <input id="email_active2" type="radio" name="email_active" value="1" @if($member['email_active']==1) checked @endif >
                                            <label for="email_active2"> {{getTranslateByKey("common_active")}} </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{getTranslateByKey("common_account_status")}}</label>
                                    <div class="form-inline">
                                        <div class="radio radio-inline radio-success">
                                            <input id="status" type="radio" name="status" value="0" @if($member['status']==0) checked @endif>
                                            <label for="status"> {{getTranslateByKey("common_disable")}} </label>
                                        </div>
                                        <div class="radio radio-inline radio-success">
                                            <input id="status1" type="radio" name="status" value="1" @if($member['status']==1) checked @endif>
                                            <label for="status1"> {{getTranslateByKey("common_enable")}} </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{getTranslateByKey("birthday")}}</label>
                                    <input type="date" name="birthday" placeholder="{{getTranslateByKey("birthday")}}" class="form-control form-control-rounded" value="{{$member['birthday']}}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{getTranslateByKey("sex")}}</label>
                            <div class="form-inline">
                                <div class="radio radio-inline radio-success">
                                    <input id="male" type="radio" name="male" value="男" @if($member['male']=='男') checked @endif>
                                    <label for="male"> {{getTranslateByKey("male")}} </label>
                                </div>
                                <div class="radio radio-inline radio-success">
                                    <input id="male1" type="radio" name="male" value="女" @if($member['male']=='女') checked @endif>
                                    <label for="male1"> {{getTranslateByKey("female")}} </label>
                                </div>
                                <div class="radio radio-inline radio-success">
                                    <input id="male2" type="radio" name="male" value="保密" @if($member['male']=='保密') checked @endif>
                                    <label for="male2"> {{getTranslateByKey("secrecy")}} </label>
                                </div>
                            </div>
                        </div>

                        <div class="myinfo__divider"></div>

                        <div class="myinfo__section-title">{{getTranslateByKey('main_security_settings')}}</div>
                        <div class="form-group">
                            <label>{{getTranslateByKey("common_login_password")}}</label>
                            <input type="text" name="password" placeholder="{{getTranslateByKey("user_if_empty_not_change")}}" class="form-control form-control-rounded">
                        </div>

                        <div style="display: flex; justify-content: flex-end; margin-top: 14px;">
                            <button type="button" id="userinfo_button" class="btn btn-primary">{{getTranslateByKey("common_submit")}}</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('admin.public.footer')


</section>
<!-- ============================================================== -->
<!-- 						Content End		 						-->
<!-- ============================================================== -->



<!-- Common Plugins -->
@include('admin/public/js',['load'=> ["custom"]])

</body>
</html>
<script>

    $(function () {

        $("#userinfo_button").click(function () {

            popup({type:'load',msg:"{{getTranslateByKey('requesting')}}",delay:800,callBack:function(){
                    $.ajax({
                        "method":"post",
                        "url":"{{url('admin/myinfo')}}",
                        "data":  new FormData($('#userinfo_form')[0]),                                  //$("#userinfo_form").serialize(),
                        "dataType":'json',
                        "cache":false,
                        "processData": false,
                        "contentType": false,
                        "success":function (res) {
                            if(res.status==200){
                                popup({type:"success",msg:res.msg,delay:2000});
                            }else{
                                popup({type:"error",msg:res.msg,delay:2000});
                            }
                        },
                        "error":function (res) {
                            var msg = res && res.responseJSON && res.responseJSON.msg ? res.responseJSON.msg : (res.status ? ("{{getTranslateByKey('main_request_failed_http', 'admin', ['status' => ':status'])}}".replace(':status', res.status)) : "{{getTranslateByKey('main_request_failed')}}");
                            popup({type:"error",msg:msg,delay:2500});
                        }
                    })
                }});



        })

    })

</script>
