# 前台调用（Frontend Invocation）

Formtools 的前台入口分两类：**模型内容页**（列表 / 详情）与**独立页面**（Page）。

## 1. 路由

| 路由 | 文件/控制器 | 说明 |
|---|---|---|
| `/p/{slug}` | `Home\PageController@show` | 独立页面（Page 模块），前台正式入口 |
| `/formtools/page/{slug}` | `Home\PageController@legacyRedirect` | 旧页面路由，301 跳转到 `/p/{slug}` |
| `/list/{access}[.html]` | 模型列表（由 FormModelTemplateManager / 主题模板承接） | 模型前台列表页 |
| `/detail/{access}/{id}[.html]` | 模型详情（同上） | 模型前台详情页 |
| `/` | 首页 | 开启首页展示的模型会按 `home_section` 场景聚合 |

> `list/detail` 是否带 `.html` 后缀由 `model_has_html_suffix($access)` 决定（与模型 SEO 配置相关）。

## 2. 助手函数（全局可用）

定义在 `app/Helper/Child/viewFunction.php`：

| 函数 | 返回 | 用途 |
|---|---|---|
| `model_list_path($access, $modelData?)` | `list/{access}[.html]` | 列表路径 |
| `model_detail_url($access, $id, $query=[], $modelData?)` | 完整 URL | 详情 URL（API 返回 `detail_url` 用它） |
| `model_detail_path($access, $id, $modelData?)` | 详情路径 | 详情路径 |
| `ModelView($viewPath, $data, $fallback)` | 视图 | 按当前主题解析并渲染模型模板 |
| `form_model_scene_template($model, $scene, $fallback)` | 模板名 | 解析某场景生效模板 |
| `form_model_home_section_view($model, $fallback)` | 模板名 | 首页区块模板 |

## 3. 主题解析链

模型列表/详情页渲染遵循：

1. 取当前主题：`cache('theme')` → 否则 `themes` 表中 `status=1` 的 identification（见 `FormModelTemplateManager::activeTheme()`）。
2. `ModelView()` 把视图交给 `themes.{theme}.model.LoadTemplate`（见 `viewFunction.php`）。
3. 场景模板由 `FormModelTemplateManager::resolveSceneTemplate()` 决定：
   - 模式 `custom` → 用 `custom_template`
   - 模式 `preset`/`visual` 其他 → 用 `template_key`（如 `list`、`cardGrid`、`detail`…）
   - 同时在 `module_formtools_model_template_maps` 中按 `model_id + scene` 命中映射
4. 可视化模式（`visual`）下，列表/详情页直接渲染 `FormModelLayout.layout_schema`（经 `PageSchemaRenderer`），不再读主题预设模板。

模板文件落点：
- 列表/详情预设模板覆盖：`public/views/themes/{theme}/model/overwrite/{template_key}.blade.php`
- 系统通用回退：`public/views/model/{template_key}.blade.php`
- 首页区块模板：`public/views/themes/{theme}/index/templates/{template_key}.blade.php`（未命中回退 `default` 主题）

## 4. 独立页面（Page）

- 后台：`Formtools → 页面管理`（`/admin/formtools/pageList`），支持新增/编辑/复制/删除/预览/设为首页。
- `FormPage` 关键字段：`slug`（前台路径 `/p/{slug}`）、`type`（`single/list/custom/landing`）、`builder_type`（`visual`/`html`）、`model_id`（可绑定模型）、`layout_schema`（可视化布局 JSON）、`page_html`/`custom_css`/`custom_js`、`seo_*`。
- 渲染：`Home\PageController::renderPage()` → `PageSchemaRenderer::render($layout_schema, $context)`，注入 `page`、`isPreview`、`publicUrl`、`layoutHtml`。
- 可视化编辑的布局结构见[主题与字段映射](./theme-field-mapping.md)中的 `layout_schema` 说明（页面与模型布局共用 `PageSchemaRenderer`）。

## 5. 最小前台调用示例

```php
// 列表链接
$listUrl = model_list_path('news');          // => /list/news 或 /list/news.html

// 详情链接
$detailUrl = model_detail_url('news', 123);  // => /detail/news/123 或 ...html

// 在 blade 中
<a href="{{ model_detail_url($model->access_identification, $record->id) }}">{{ $record->title }}</a>
```

> 注意：`model_detail_url` 第一个参数是 `access_identification`，不是数据库 `id`，也不是 `identification`。
