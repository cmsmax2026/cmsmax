# Formtools 模块使用手册

> 面向开发者的模块文档（含代码/术语）。运营侧说明请以后台界面为准。
> 模块标识：`Formtools`，版本 `2.1.4`，类型 `function`（基础模块，不可禁用/卸载）。

Formtools 是一个低代码的内容模型 / 字段 / 页面构建模块，核心能力：

- **模型（Model）**：动态建表（`module_formtools_{identification}`），支持单模型（single）与多模型（multi）。
- **字段（Field）**：文本、富文本、数字、下拉、单选、多选、上传、图片、颜色、标签、日期、JSON 等 50+ 类型。
- **开放 API（OpenAPI）**：把任意模型以 `meta` + `submit` 两个接口对外暴露，带应用鉴权、IP 白名单、限流、幂等。
- **主题与字段映射**：基于「语义别名」自动识别字段（如 `title`/`name` → 标题），把模型字段映射到模板，三模式 `preset/visual/custom`。
- **页面（Page）**：`/p/{slug}` 多页面编排，支持可视化布局（`layout_schema` JSON）。
- **模型可视化布局**：列表页 / 详情页 / 首页区块均可切换为可视化布局，直接渲染 `layout_schema`。

## 文档导航

| 文档 | 内容 |
|---|---|
| [api-guide.md](./api-guide.md) | **外部 API 调用手册**（meta/submit、应用鉴权、IP 白名单、限流、幂等、示例） |
| [model-field-config.md](./model-field-config.md) | 模型配置、字段配置、字段类型与字段属性 |
| [frontend-invocation.md](./frontend-invocation.md) | 前台调用：list/detail/page 路由、ModelView、主题解析链 |
| [theme-field-mapping.md](./theme-field-mapping.md) | 主题与字段映射快速写法：别名、注册表、自动探测、三模式 |

## 快速开始

### 1. 开启开放 API（全局）
在 `module_system_settings`（`module=Formtools`）中确保以下键存在且合理：

| 键 | 说明 | 默认 |
|---|---|---|
| `openapi_enabled` | 全局开关，1=启用 | 1 |
| `openapi_idempotent_ttl` | 幂等键有效期（秒） | 600 |
| `openapi_rate_limit` | 全局每分钟上限（取与应用的较小值） | 60 |
| `openapi_ip_whitelist` | 全局 IP 白名单（空=不限制） | 空 |

> 这些值由 `FormOpenApiService::globalSettings()` 读取；缺省时回退到上方默认值。

### 2. 创建模型并启用 API
后台 `Formtools → 模型管理` 新建模型，记住：
- `identification`：物理表名 = `module_formtools_{identification}`
- `access_identification`：对外访问标识，API 与列表/详情前台路由都用它
- 模型「开放 API」配置（`other_config.openapi`）：`enabled=1`、`allow_seo`、`default_status`、`ip_whitelist`

### 3. 添加字段
后台 `Formtools → 字段管理` 添加字段，至少保证一个必填字段（否则 submit 会报「没有可写入的模型字段」）。

### 4. 创建开放应用
后台 `Formtools → 模型管理 → 开放 API（modelApi）`，为该模型新建应用，拿到 `app_id` 与 `app_secret`（secret 只在创建/重置时明文展示一次）。

### 5. 调用 API
```bash
# 拉取字段元数据（无需鉴权，只要模型和全局都启用）
curl https://你的域名/api/formtools/open/{access_identification}/meta

# 提交数据（用 app_id + app_secret 鉴权）
curl -X POST https://你的域名/api/formtools/open/{access_identification}/submit \
  -H "Content-Type: application/json" \
  -H "X-Formtools-AppId: fta_xxx" \
  -H "X-Formtools-AppSecret: fts_xxx" \
  -H "X-Formtools-Idempotency-Key: req-20260902-0001" \
  -d '{"title":"示例标题","content":"正文内容"}'
```

详见 [api-guide.md](./api-guide.md)。

## 架构速览

```
Formtools
├─ 模型   FormModel (module_formtools_models)
│         ├─ fields(JSON)             字段定义
│         ├─ other_config(JSON)       含 openapi / home_config / seo 配置
│         └─ access_identification    对外标识
├─ 字段   由 FormTool 渲染，存于模型.fields
├─ 开放API OpenApiApp / OpenApiLog
│         ├─ meta  GET   公开字段元数据
│         └─ submit POST 写入数据（app_id+app_secret 鉴权）
├─ 模板   FormTemplateRegistry   场景模板注册（list/detail/home_section）
│         FormModelTemplateMap   模型→场景→字段映射+模式
├─ 页面   FormPage / FormPageCategory  （/p/{slug}，可视化 layout_schema）
└─ 布局   FormModelLayout       模型可视化布局（theme_tokens + layout_schema）
```

## 常用入口

| 能力 | 后台入口 | 前台入口 |
|---|---|---|
| 模型管理 | `/admin/formtools/index` | `/list/{access}[.html]` |
| 字段管理 | `/admin/formtools/fieldList` | — |
| 开放 API | `/admin/formtools/modelApi` | `api/formtools/open/{access}/meta`、`/submit` |
| 页面管理 | `/admin/formtools/pageList` | `/p/{slug}` |
| 模型布局 | `/admin/formtools/modelLayoutBuilder` | `/list/{access}`、`/detail/{access}/{id}`（可视化模式时） |
