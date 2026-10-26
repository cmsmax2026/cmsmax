@include("admin.public.header")
<body class="horizontal">

@include("admin.func.themeSubNav", ['theme' => $theme, 'active' => $active, 'pageData' => $pageData])

<section class="container-fluid pl-5 pr-5 ">

    <div class="row pb-5">
        <div class="col-12 bg-light p-2" style="min-height: calc(100vh - 160px)">
            <iframe src="{{url("admin/theme/preview?m=".$theme)}}" class="" style="width: 100%;height: 100%;border: none;"></iframe>
        </div>
    </div>
    @include('admin.public.footer')
</section>
@include('admin.public.js',['load'=> ["custom"]])
</body>
</html>
