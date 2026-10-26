# 外部 API 调用手册（OpenAPI）

Formtools 可把任意模型以两个 HTTP 接口对外暴露：**拉取字段元数据**（`meta`）与**提交数据**（`submit`）。
后台路径：`Formtools → 模型管理 → 开放 API`（路由 `/admin/formtools/modelApi`）。

## 1. 基础信息

| 项 | 值 |
|---|---|
| 元数据接口 | `GET  {SITE}/api/formtools/open/{access_identification}/meta` |
| 提交接口 | `POST {SITE}/api/formtools/open/{access_identification}/submit` |
| 全局开关 | `module_system_settings(module=Formtools, key=openapi_enabled)` = 1 |
| 模型开关 | 模型 `other_config.openapi.enabled` = 1 |
| 响应格式 | JSON：`{ "status": 200, "msg": "...", "data": {...} }`（由 `returnArr()` 产出） |

> `{access_identification}` 是模型的 `access_identification`（非数据库 `id`、非 `identification`）。

## 2. 全局与应用配置

### 全局设置（module_system_settings，module=Formtools）
由 `FormOpenApiService::globalSettings()` 读取：
- `openapi_enabled`（默认 1）
- `openapi_idempotent_ttl`（默认 600 秒）
- `openapi_rate_limit`（默认 60 次/分钟）
- `openapi_ip_whitelist`（默认空，逗号/IP/CIDR 分隔）

### 模型配置（模型.other_config.openapi）
- `enabled`：是否对该模型开放（默认 0，**必须手动开启**）
- `allow_seo`：是否允许提交 `seo_title/seo_keywords/seo_description`（默认 1）
- `default_status`：写入后的记录状态 0/1/2（默认 1）
- `ip_whitelist`：模型级 IP 白名单
- `remark`：备注

### 应用（OpenApiApp，每个模型可建多个）
- `app_id`（形如 `fta_xxx`）、`app_secret`（形如 `fts_xxx`，密文存储，创建/重置时明文展示一次）
- `status`：1=启用
- `rate_limit`：应用级每分钟上限（实际生效 = min(应用, 全局)）
- `ip_whitelist`：应用级 IP 白名单
- `last_used_at` / `last_ip`：最近调用

## 3. 鉴权（简单 AppId + AppSecret）

`submit` 当前**直接比对 `app_secret` 明文**，头部约定如下：

| Header | 必填 | 说明 |
|---|---|---|
| `X-Formtools-AppId` | 是 | 应用 `app_id` |
| `X-Formtools-AppSecret` | 是 | 应用 `app_secret` 明文（用 `hash_equals` 比对） |
| `X-Formtools-Idempotency-Key` | 强烈建议 | 幂等键，相同键在 `openapi_idempotent_ttl` 内重复提交直接 409 |

> 说明：请求体里也能用平铺字段 `app_id`/`app_secret`/`idempotency_key` 代替对应 Header。

校验顺序（任一不满足即返回对应错误码）：
1. 全局未启用 → 403
2. 模型未启用 → 403
3. 缺少 `app_id`/`app_secret` → 422
4. 应用不存在 / 已停用 → 403
5. IP 不在（全局∪模型∪应用）白名单 → 403
6. 超过限流（min 应用/全局）→ 429
7. `app_secret` 比对失败 → 403
8. 幂等键已使用 → 409

## 4. 请求体（submit）

支持两种形态，任选其一：
- **平铺字段**：`{ "title": "xx", "content": "yy" }`
- **统一包体**：`{ "payload": "{\"title\":\"xx\"}" }`（JSON 字符串），服务端会 `json_decode` 后使用

以下键为保留键，提交时会被忽略（不会写入数据表）：
`app_id`、`app_secret`、`payload`、`idempotency_key`。

字段写入规则（见 `OpenApiController::buildInsertData`）：
- `id` 字段被跳过；`status/remark/seo_*` 由系统控制，不接收外部值
- 必填字段（`required`）为空 → 该字段报错
- 数字/整型字段收到非数字 → 校验失败
- 超过 `maxlength` → 校验失败
- 有选项（`datas`）的字段，值不在选项集合内 → 校验失败
- 数组值用逗号拼接为字符串后写入
- 自动补：`status`（按 `default_status`）、`uid=0`、`created_at`、`updated_at`
- 主键非自增字符串类型时，自动生成 `ftc_xxx` 记录 ID

## 5. meta 响应示例

```json
{
  "status": 200,
  "msg": "获取成功",
  "data": {
    "model": { "id": 12, "name": "新闻", "identification": "news", "access_identification": "news" },
    "config": { "enabled": 1, "allow_seo": 1, "default_status": 1, "ip_whitelist": "", "remark": "" },
    "endpoints": {
      "meta":   { "method": "GET",  "url": "https://域名/api/formtools/open/news/meta" },
      "submit": { "method": "POST", "url": "https://域名/api/formtools/open/news/submit" }
    },
    "authentication": { "type": "app_secret", "headers": ["X-Formtools-AppId", "X-Formtools-AppSecret", "X-Formtools-Idempotency-Key"] },
    "fields": [
      { "identification": "title", "name": "标题", "formtype": "text", "required": true, "maxlength": 120, "datas": [] },
      { "identification": "content", "name": "正文", "formtype": "editor", "required": false, "maxlength": 0, "datas": [] }
    ],
    "meta_fields": [
      { "identification": "seo_title", "name": "SEO 标题", "formtype": "text", "required": false, "remark": "选填" }
    ]
  }
}
```

`fields` 为内容字段（不含 `id/status/remark/seo_*`）；`meta_fields` 为 `allow_seo=1` 时可选的 SEO 字段。

## 6. submit 响应示例

成功：
```json
{ "status": 200, "msg": "提交成功", "data": { "id": 123, "detail_url": "https://域名/detail/news/123.html" } }
```
失败：
```json
{ "status": 422, "msg": "标题 不能为空；内容 存在未定义的选项值", "data": null }
```

`detail_url` 由 `model_detail_url($access, $id)` 生成（受 `model_has_html_suffix` 影响是否带 `.html`）。

## 7. 调用示例

### curl
```bash
# meta
curl https://域名/api/formtools/open/news/meta

# submit
curl -X POST https://域名/api/formtools/open/news/submit \
  -H "Content-Type: application/json" \
  -H "X-Formtools-AppId: fta_你的appid" \
  -H "X-Formtools-AppSecret: fts_你的secret" \
  -H "X-Formtools-Idempotency-Key: req-$(date +%s)-1" \
  -d '{"title":"示例新闻标题","content":"正文内容","seo_title":"SEO 标题"}'
```

### JavaScript（fetch）
```js
const SITE = 'https://域名';
const ACCESS = 'news';
const APP_ID = 'fta_你的appid';
const APP_SECRET = 'fts_你的secret';

// 拉取元数据
const meta = await (await fetch(`${SITE}/api/formtools/open/${ACCESS}/meta`)).json();

// 提交
const res = await fetch(`${SITE}/api/formtools/open/${ACCESS}/submit`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-Formtools-AppId': APP_ID,
    'X-Formtools-AppSecret': APP_SECRET,
    'X-Formtools-Idempotency-Key': 'req-' + Date.now() + '-' + Math.random().toString(36).slice(2),
  },
  body: JSON.stringify({ title: '示例新闻标题', content: '正文内容' }),
}).then(r => r.json());
console.log(res);
```

### PHP（原生）
```php
function formtoolsSubmit(string $site, string $access, string $appId, string $appSecret, array $data): array {
    $url = rtrim($site, '/') . "/api/formtools/open/{$access}/submit";
    $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Formtools-AppId: ' . $appId,
            'X-Formtools-AppSecret: ' . $appSecret,
            'X-Formtools-Idempotency-Key: req-' . uniqid('', true),
        ],
        CURLOPT_POSTFIELDS => $body,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode((string)$resp, true) ?: [];
}
```

## 8. 后台应用管理

| 动作 | 路由 | 说明 |
|---|---|---|
| 查看/配置 | `/admin/formtools/modelApi?id={modelId}` | 模型开放 API 总览与配置 |
| 新建应用 | `POST /admin/formtools/modelApi/appCreate` | 生成 `app_id` + 一次性 `app_secret` |
| 启用/停用 | `POST /admin/formtools/modelApi/appStatus` | 切换应用 `status` |
| 重置密钥 | `POST /admin/formtools/modelApi/resetSecret` | 重新生成 `app_secret`（旧密钥立即失效） |

## 9. 日志与排查

- 调用日志落表 `module_formtools_openapi_logs`（`OpenApiLog`），记录 `request_id`、`app_id`、`status_code`、`client_ip`、请求/响应体（截断）。
- 常见错误：
  - `404 模型不存在`：检查 `access_identification`
  - `403 当前模型未启用开放 API`：模型 `other_config.openapi.enabled` 未开
  - `422 缺少鉴权请求头`：未传 `app_id`/`app_secret`
  - `429 调用频率过高`：超过 `min(应用限流, 全局限流)`
  - `409 幂等键已使用`：同一 `X-Formtools-Idempotency-Key` 重复提交
