@php($toTopView = (isset($moduleName) && $moduleName) ? moduleAdminTemplate($moduleName)."public.toTop" : 'system::admin.public.toTop')
@if(view()->exists($toTopView))
    @include($toTopView)
@else
    @include('system::admin.public.toTop')
@endif
