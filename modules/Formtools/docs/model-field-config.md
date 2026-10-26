# 模型配置与字段配置

## 1. 模型（FormModel）

后台入口：`Formtools → 模型管理`（`/admin/formtools/index`）。

### 关键字段
- `name`：模型名称（展示用）
- `identification`：模型标识，**物理表名 = `module_formtools_{identification}`**
- `access_identification`：对外访问标识，用于 API（`/meta`、`/submit`）与前台路由（`/list/{access}`、`/detail/{access}/{id}`）
- `type`：`single`（单模型）/ `multi`（多模型）
- `fields`：JSON 数组，存放全部字段定义
- `other_config`：JSON，含 `openapi`、`home_config`、`home_seo_config`、`home_seo_detail_config`、`admin_config` 等子配置
- `show_home_page`：是否参与首页区块聚合

### 保留标识（不可用作 identification）
以下 `identification` 被 `FormModel::RESERVED_IDENTIFICATIONS` 占用，创建模型时会自动排除：
`models`、`model_layouts`、`model_template_maps`、`dynamic_tables`、`pages`、`page_categories`、`template_registry`、`openapi_apps`、`openapi_logs`、`paid_records`。

### 常用动作
| 动作 | 路由 | 说明 |
|---|---|---|
| 列表 | `/admin/formtools/index` | 模型列表（含首页接管状态） |
| 新建 | `/admin/formtools/modelAdd` | 新建模型并自动建表 |
| 编辑 | `/admin/formtools/modelEdit` | 编辑模型元信息 |
| 字段列表 | `/admin/formtools/fieldList` | 管理该模型字段 |
| 开放 API | `/admin/formtools/modelApi` | API 配置 + 应用管理 |
| 可视化布局 | `/admin/formtools/modelLayoutBuilder` | 列表/详情/首页区块可视化编辑 |
| 反推结构 | `/admin/formtools/getModel` | 从已有数据表反推模型结构 |
| 演示数据 | `/admin/formtools/seedDemoContent` | 灌入演示数据 |
| 重建 | `/admin/formtools/resetModelData` | 重建结构与数据 |

## 2. 字段（Field）

字段存于模型 `fields` JSON。每个字段对象典型结构：

| 键 | 说明 |
|---|---|
| `identification` | 字段名（对应数据表列名），字母/数字/下划线 |
| `name` | 字段显示名 |
| `remark` | 备注/提示 |
| `formtype` | 表单控件类型（见下表） |
| `fieldtype` | 底层数据类型（text/integer/biginteger/float/double/decimal…） |
| `required` | 是否必填（true/false/1/0/"required"… 会被 `normalizeFieldRequired` 归一） |
| `maxlength` | 最大长度（0=不限制） |
| `datas` | 选项数组（select/radio/checkbox 等用），形如 `[{"name":"选项A","value":"a"}]` 或 `["a","b"]` |

### 字段类型（FormTool 支持的控件）
通过 `Modules\Formtools\Http\Controllers\Admin\FormTool`（类 `FormTool`）渲染，常用 `field()` 与具体类型方法：

| formtype | 方法 | 说明 |
|---|---|---|
| `text` | `text()` | 单行文本 |
| `textarea` | `textarea()` | 多行文本 |
| `editor` | `editor()` | 富文本 |
| `hidden` | `hidden()` | 隐藏域 |
| `number` | `number()` | 数字 |
| `select` | `select()` | 下拉（需 `datas`） |
| `radio` | `radio()` | 单选（需 `datas`） |
| `checkbox` | `checkbox()` | 多选（需 `datas`） |
| `upload` | `upload()` | 文件上传 |
| `image` | `image()` | 图片 |
| `color` | `color()` | 颜色选择 |
| `json` | `json()` | JSON 编辑 |
| `tags` | `tags()` | 标签 |
| `date` | `date()` | 日期 |

> 更多类型（relation/city/map/cascader/slider/rate 等）以 `FormTool` 实际暴露的方法为准；
> API 侧只关心 `formtype`/`fieldtype`/`required`/`maxlength`/`datas` 这五个属性。

### API 校验与字段类型的关系
外部 `submit` 校验逻辑（`OpenApiController`）：
- `formtype=number` 或 `fieldtype∈{integer,biginteger,float,double,decimal}` → 必须是数字
- `maxlength>0` → 字符串长度上限
- 有 `datas` 选项 → 值必须在选项集合内（数组则每个元素都要合法）
- `required` 且为空 → 报错

## 3. 数据表约定

- 数据表：`module_formtools_{identification}`
- 系统列（外部 API 不接收、由系统填充）：`id`、`status`、`uid`、`created_at`、`updated_at`
- SEO 列（仅当模型 `allow_seo=1` 时可由 API 写入）：`seo_title`、`seo_keywords`、`seo_description`
- 主键为字符串非自增时，`submit` 自动生成 `ftc_xxxx` 记录 ID
