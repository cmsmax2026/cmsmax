# 主题与字段映射快速指南

Formtools 用「语义别名 + 自动探测」把**模型字段**映射到**模板**，让你无需逐字段绑定即可套用主题模板。
核心三件套：

- `FormTemplateCatalog`：场景与别名的「字典」
- `FormTemplateRegistry`：每个场景可用的模板（含必需/推荐字段别名）
- `FormModelTemplateMap`：每个模型 × 每个场景的「实际映射 + 模式」

## 1. 三个场景（Scene）

| 场景 | 常量 | 前台路由 | 说明 |
|---|---|---|---|
| 列表页 | `list` | `/list/{access}[.html]` | 渲染多条，承接分页 |
| 详情页 | `detail` | `/detail/{access}/{id}[.html]` | 渲染单条 |
| 首页区块 | `home_section` | `/` | 开启首页展示后聚合到首页 |

## 2. 语义别名（fieldAliases）

`FormTemplateCatalog::fieldAliases()` 把「语义」映射到「候选字段名」，系统按模型实际字段名自动匹配：

| 别名 | 候选字段（命中其一即可） |
|---|---|
| `title_alias` | `title`, `name` |
| `summary_alias` | `summary`, `describe`, `description`, `intro`, `remark` |
| `content_alias` | `content`, `body`, `detail` |
| `cover_alias` | `cover`, `image`, `thumb`, `pic`, `logo` |
| `date_alias` | `date`, `publish_time`, `created_at`, `updated_at` |
| `pid_alias` | `pid`, `parent_id` |
| `gallery_alias` | `images`, `gallery` |
| `video_alias` | `video`, `video_url`, `media_url` |
| `phone_alias` | `phone`, `tel`, `mobile` |
| `email_alias` | `email`, `mail` |
| `address_alias` | `address`, `company_address` |
| `link_alias` | `url`, `link`, `href` |

> **快速建字段的诀窍**：把字段 `identification` 直接命名为上表的候选词（如 `title`、`cover`、`content`），
> 系统 `detectFieldMap()` 会自动完成映射，模板即可「零配置」生效。

## 3. 模板注册表（FormTemplateRegistry）

每个模板记录：
- `scene`：`list` / `detail` / `home_section`
- `template_key`：模板键，如 `list`、`cardGrid`、`detail`、`contacts`
- `template_name` / `description`：展示名与说明
- `required_fields`：必需别名数组（缺失则标「有缺口」）
- `recommended_fields`：推荐别名数组
- `supported_model_type`：`['single','multi']` 支持的类型
- `sort` / `status`

后台「模板匹配」面板（`FormModelTemplateManager::buildTemplateGuideHtml()`）会按当前模型字段，
实时显示每个模板的「完全匹配 / 基本匹配 / 有缺口 / 类型不匹配」，并列出命中了哪些别名。

## 4. 三种渲染模式（mode）

每个模型 × 每个场景可设一种模式（`FormModelTemplateMap.mode`）：

| 模式 | 行为 |
|---|---|
| `preset` | 用 `template_key` 指定的主题预设模板（默认） |
| `visual` | 直接渲染该场景的 `layout_schema`（可视化布局），不再读主题模板 |
| `custom` | 用 `custom_template` 指定的自定义模板文件名 |

切换位置：模型「模板/字段映射」面板（写 `module_formtools_model_template_maps`，由 `syncModelTemplateMaps()` 维护）。

## 5. 快速写一个主题模板

### 列表/详情模板（覆盖系统默认）
把 blade 放到：`public/views/themes/{你的主题}/model/overwrite/{template_key}.blade.php`
例如 `cardGrid`：`public/views/themes/mytheme/model/overwrite/cardGrid.blade.php`

模板内通过字段别名取数（系统已把映射结果注入 `$data`），典型写法：

```blade
{{-- cardGrid.blade.php --}}
@foreach($records as $item)
  <a href="{{ model_detail_url($access, $item->id) }}" class="card">
    @if(!empty($item->cover))
      <img src="{{ $item->cover }}" alt="{{ $item->title }}">
    @endif
    <h3>{{ $item->title }}</h3>
    @if(!empty($item->summary))<p>{{ $item->summary }}</p>@endif
    @if(!empty($item->date))<time>{{ $item->date }}</time>@endif
  </a>
@endforeach
```

> 字段名用你**真实的 `identification`**（如 `cover`/`title`/`summary`），而不是别名本身。
> 别名只是「自动匹配用」的语义层，渲染时已经是具体字段值。

### 首页区块模板
放到：`public/views/themes/{你的主题}/index/templates/{template_key}.blade.php`

### 可视化布局（layout_schema）
`visual` 模式下不再用 blade，而是渲染 `layout_schema` JSON（结构见下）。
页面与模型布局共用 `PageSchemaRenderer`。

`layout_schema` 顶层：
```json
{
  "theme": {
    "primary": "#2563eb",
    "accent": "#0f172a",
    "surface": "#ffffff",
    "radius": "20px",
    "shadow": "0 16px 34px rgba(15,23,42,0.08)"
  },
  "sections": [
    {
      "type": "section",
      "props": { "contentWidth": "contained", "innerWidth": "1200px", "anchor": "home-news" },
      "style": { "padding": "72px 0", "background": "#ffffff" },
      "children": [ ... ]
    }
  ]
}
```

常用区块（block）类型（在 `section.children` 或 `column.blocks` 中）：
`heading`、`text`、`button`、`image`、`row`、`column`、`stats`、`cta`、`qrcode`、
`model_list`（属性 `model`/`limit`/`template`/`title`）、`model_detail`（属性 `model`/`template`/`title`）。

默认 schema 由 `FormTemplateCatalog::defaultSceneSchema($scene, $access, $title, $desc)` 生成，
可在后台可视化编辑器里直接改，保存到 `FormModelLayout.layout_schema`（模型）或 `FormPage.layout_schema`（页面）。

## 6. 一条龙：新模型快速套主题

1. 建模型，把关键字段命名为 `title`/`cover`/`content`/`summary`/`date`（自动命中别名）。
2. 后台「模板/字段映射」面板选择场景模板（如列表选 `cardGrid`、详情选 `detail`），面板会显示匹配度。
3. 主题里放好对应 `model/overwrite/{template_key}.blade.php`。
4. 如需完全自定义版式，把场景切到 `visual`，用可视化编辑器拖出 `layout_schema`。
