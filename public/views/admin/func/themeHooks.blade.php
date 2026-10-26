@include("admin.public.header")
<body class="horizontal">

@include("admin.func.themeSubNav", ['theme' => $theme, 'active' => 'hooks', 'pageData' => $pageData])

<section class="container-fluid pl-5 pr-5">
    <div class="row pb-5">
        <div class="col-12">
            <div class="alert alert-info">
                {!! getTranslateByKey('main_hooks_intro_1') !!}
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">{{getTranslateByKey('main_hooks_declared_title', 'admin', ['theme' => $theme])}}</h6>
                </div>
                <div class="card-body">
                    @if(empty($declared))
                        <span class="text-muted">{{getTranslateByKey('main_hooks_not_declared')}}</span>
                    @else
                        @foreach($declared as $name)
                            <code class="mr-3 d-inline-block mb-1">{{ $name }}</code>
                        @endforeach
                    @endif
                    <div class="small text-muted mt-3">
                        {{getTranslateByKey('main_hooks_append_guide')}}
                        <pre class="p-2 bg-light mt-1 mb-0" style="font-size:12px;">"hooks": ["ThemeHead", "ThemeFooter", "ThemeData", "MyOwnHook"]</pre>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">{{getTranslateByKey('main_hooks_builtin')}}</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th style="width:180px;">{{getTranslateByKey('main_hook_name')}}</th>
                            <th style="width:220px;">{{getTranslateByKey('main_hook_trigger_where')}}</th>
                            <th style="width:220px;">{{getTranslateByKey('main_hook_returns')}}</th>
                            <th>{{getTranslateByKey('main_label_desc')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($catalog as $hook)
                            <tr>
                                <td><code>{{ $hook['name'] }}</code></td>
                                <td class="small">{{ $hook['where'] }}</td>
                                <td class="small text-muted">{{ $hook['returns'] }}</td>
                                <td class="small">{{ $hook['description'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><h6 class="mb-0">{{getTranslateByKey('main_hooks_how_listen')}}</h6></div>
                <div class="card-body">
                    <p class="mb-2">{{getTranslateByKey('main_hooks_listen_desc')}}</p>
                    <pre class="p-3 bg-light" style="font-size:12px;">&lt;?php
namespace Plugins\Demo\Events;

class ThemeHead
{
    public function __construct($data)
    {
        // $data['theme'] 当前主题标识
    }
}</pre>
                    <p class="mb-0 small text-muted mt-2">
                        {!! getTranslateByKey('main_hooks_trigger_desc') !!}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

@include('admin.public.footer')
@include('admin.public.js', ['load' => ["custom"]])
</body>
</html>
