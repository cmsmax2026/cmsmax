<?php

namespace App\Support\Theme;

/**
 * 主题钩子注册表。
 *
 * 「主题的钩子列表可以另外设定」：
 *  - 系统内置一组钩子点（下面的 available()），主题按需在模板里触发；
 *  - 主题也可以在自己的 config.json 里用 "hooks": [...] 声明本主题支持的钩子，
 *    后台会展示这份清单，插件据此知道能挂什么；
 *  - 插件监听方式与其它钩子一致：Modules\<X>\Listeners\<HookName>.php。
 *
 * 触发方式（模板里）：
 *     {!! theme_hook('ThemeHead') !!}
 *     {mx:hook name="ThemeHead" /}
 */
class ThemeHooks
{
    /**
     * 内置钩子点清单。
     *
     * @return array<int, array{name:string,where:string,returns:string,description:string}>
     */
    public static function available(): array
    {
        return [
            ['name' => 'ThemeHead', 'where' => '主题 head 模板 </head> 之前', 'returns' => 'HTML 字符串', 'description' => '注入 CSS / meta / 统计代码，最常用的挂载点'],
            ['name' => 'ThemeFooter', 'where' => '主题底部 </body> 之前', 'returns' => 'HTML 字符串', 'description' => '注入 JS / 客服挂件 / 浮窗'],
            ['name' => 'ThemeRenderBefore', 'where' => '页面内容渲染前', 'returns' => 'HTML 字符串', 'description' => '在主题主体内容之前插入区块'],
            ['name' => 'ThemeRenderAfter', 'where' => '页面内容渲染后', 'returns' => 'HTML 字符串', 'description' => '在主题主体内容之后插入区块'],
            ['name' => 'ThemeData', 'where' => 'theme_data() 找不到提供者时', 'returns' => '任意数据；返回 null 表示不处理', 'description' => '插件向主题提供数据，主题从而能调用任意模块数据'],
            ['name' => 'ThemeShortTag', 'where' => '短标签解析遇到未知标签', 'returns' => 'HTML 字符串；null 表示不处理', 'description' => '给主题增加自定义短标签 {mx:xxx}'],
            ['name' => 'ThemeShortTagCatalog', 'where' => '后台短标签生成器', 'returns' => '标签说明数组', 'description' => '把自定义标签写进后台标签清单'],
            ['name' => 'ThemeSettingSchema', 'where' => '读取主题设置项定义', 'returns' => '配置项数组', 'description' => '给主题设置页追加配置项，无需改主题的 config.json'],
            ['name' => 'ThemeCssVariables', 'where' => '生成主题 CSS 变量', 'returns' => "['--my-var' => 'value']", 'description' => '追加自定义 CSS 变量'],
            ['name' => 'ThemeUpgradeFile', 'where' => '主题升级逐文件决策', 'returns' => "['action'=>'slot|overwrite','reason'=>'']", 'description' => '决定某个文件是否被升级覆盖，slot 表示放进更新槽位'],
            ['name' => 'ThemeMenu', 'where' => '主题渲染菜单时', 'returns' => '菜单项数组', 'description' => '向前台菜单注入自定义菜单项'],
            ['name' => 'ThemeSlotResolved', 'where' => '更新槽位被应用或丢弃后', 'returns' => '忽略', 'description' => '通知插件同步自己的缓存或派生文件'],
        ];
    }

    /**
     * 内置钩子点名称列表。
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_column(self::available(), 'name');
    }

    /**
     * 主题在 config.json 里声明的钩子列表；没声明时返回内置列表。
     *
     * @return array<int, string>
     */
    public static function forTheme(?string $theme = null): array
    {
        $manifest = ThemeManager::manifest($theme);
        $hooks = (array) ($manifest['hooks'] ?? []);

        $hooks = array_values(array_filter(array_map(static function ($item) {
            return is_array($item) ? (string) ($item['name'] ?? '') : (string) $item;
        }, $hooks)));

        return $hooks !== [] ? $hooks : self::names();
    }

    /**
     * 触发一个主题钩子，把各监听器的返回值拼接成字符串。
     */
    public static function render(string $name, array $params = [], ?string $theme = null): string
    {
        if ($name === '') {
            return '';
        }

        $html = '';
        foreach ((array) hook($name, array_merge(['theme' => $theme ?: ThemeManager::current()], $params)) as $item) {
            if ($item !== null) {
                $html .= (string) $item;
            }
        }

        return $html;
    }
}
