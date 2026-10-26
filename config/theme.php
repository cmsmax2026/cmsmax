<?php

return [
    /*
     * 主题数据查询（theme_cms_list / detail / chart / count / tree 等短标签）的
     * 跨请求结果缓存秒数。
     *
     *   0     = 关闭（每次请求都查库，适合内容需近实时更新的场景）。默认。
     *   300   = 生产建议值（5 分钟），大幅降低数据库压力。
     *
     * 也可在单个短标签上用 cache 属性覆盖，例如：
     *   {mx:list model=news limit=10 cache=120}   缓存 120 秒
     *   {mx:list model=news limit=10 cache=0}     该标签强制实时（不缓存）
     *
     * 失效策略：缓存 key 含「模型版本号」。内容变更后调用
     *   \App\Support\Theme\FormtoolsDataProvider::bump($accessIdentification)
     * 即可让该模型相关缓存立即失效（版本号自增，旧 key 自然失活）。
     * 未接入 bump 时，靠 TTL 自然过期，最多延迟 TTL 秒看到更新。
     *
     * 注：无论此处如何设置，「请求内去重」始终生效——同一页面内相同查询只查一次库。
     */
    'data_cache_ttl' => 0,
];
