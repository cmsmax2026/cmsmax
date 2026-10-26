{{--
    驱动来源选择卡片组。

    参数：
    $gridId       网格容器 id（同时用于选中态同步）
    $driverKey    设置键，如 sms_driver
    $driverLabel  能力名称，用于空态提示，如「短信服务」
    $driverPlugins 可选插件列表（hook Get*PluginList 的结果）
    $optionDesc   每个插件卡片上的说明文字
    $emptyValue   「未配置」选项提交的值（可为空串，也可为 System 这类内置标识）
    $emptyTitle   「未配置」选项的标题，如 暂不启用 / 系统内置
    $emptyDesc    「未配置」选项的说明
    $showEmptyOption 是否显示「未配置」选项；当插件列表里已经包含内置来源时传 false，
                     否则会出现两个取值相同、同时被选中的重复项
--}}
@php
    $showEmptyOption = $showEmptyOption ?? true;
    $gridId = $gridId ?? 'driverGrid';
    $driverKey = $driverKey ?? '';
    $driverLabel = $driverLabel ?? '该能力';
    $driverPlugins = $driverPlugins ?? [];
    $optionDesc = $optionDesc ?? '';
    $emptyValue = $emptyValue ?? '';
    $emptyTitle = $emptyTitle ?? '暂不启用';
    $emptyDesc = $emptyDesc ?? '不选择任何插件，该功能将保持未配置状态。';
    $currentDriver = $driverKey !== '' ? (string) __E($driverKey) : '';
@endphp
<div class="setting-driver-grid" id="{{$gridId}}" data-driver-grid>
    @if(!empty($driverPlugins))
        @foreach($driverPlugins as $plugin)
            @if(!empty($plugin['identification']))
                <label class="setting-driver-option @if($currentDriver !== '' && $currentDriver === (string) $plugin['identification']) is-active @endif">
                    <input type="radio"
                           value="{{$plugin['identification']}}"
                           name="{{$driverKey}}"
                           @if($currentDriver !== '' && $currentDriver === (string) $plugin['identification']) checked @endif >
                    <span class="setting-driver-option__title">{{$plugin['name'] ?? $plugin['identification']}}</span>
                    @if($optionDesc)
                        <span class="setting-driver-option__desc">{{$optionDesc}}</span>
                    @endif
                </label>
            @endif
        @endforeach

        @if($showEmptyOption)
            <label class="setting-driver-option @if($currentDriver === (string) $emptyValue) is-active @endif">
                <input type="radio"
                       value="{{$emptyValue}}"
                       name="{{$driverKey}}"
                       @if($currentDriver === (string) $emptyValue) checked @endif >
                <span class="setting-driver-option__title">{{$emptyTitle}}</span>
                <span class="setting-driver-option__desc">{{$emptyDesc}}</span>
            </label>
        @endif
    @else
        <div class="setting-driver-empty">
            还没有安装任何「{{$driverLabel}}」插件，这里暂时没有可选项。<br>
            请先到 <a href="{{url('admin/plugin')}}" target="_blank">插件管理</a> 安装并启用对应插件，再回来选择。
        </div>
    @endif
</div>
