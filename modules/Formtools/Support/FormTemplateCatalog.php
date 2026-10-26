<?php

namespace Modules\Formtools\Support;

class FormTemplateCatalog
{
    public const SCENE_LIST = 'list';
    public const SCENE_DETAIL = 'detail';
    public const SCENE_HOME_SECTION = 'home_section';

    public static function scenes(): array
    {
        return [
            self::SCENE_LIST => '列表页',
            self::SCENE_DETAIL => '详情页',
            self::SCENE_HOME_SECTION => '首页区块',
        ];
    }

    public static function sceneMeta(): array
    {
        return [
            self::SCENE_LIST => [
                'label' => '列表页',
                'route' => '/list/{access}[.html]',
                'summary' => '模型前台列表页，负责渲染多条内容，支持分页和列表搜索。',
                'template_scope' => '优先走当前主题的 model/overwrite，其次回退到系统通用 model 模板。',
            ],
            self::SCENE_DETAIL => [
                'label' => '详情页',
                'route' => '/detail/{access}/{id}[.html]',
                'summary' => '单条内容详情页，只渲染一条记录，不参与分页。',
                'template_scope' => '优先走当前主题的 model/overwrite，其次回退到系统通用 detail 模板。',
            ],
            self::SCENE_HOME_SECTION => [
                'label' => '首页区块',
                'route' => '/',
                'summary' => '站点首页的聚合展示区块，只有模型开启首页展示后才会汇总到首页。',
                'template_scope' => '预设模式走当前主题的 index/templates；可视化模式直接渲染 layout_schema。',
            ],
        ];
    }

    public static function fieldAliases(): array
    {
        return [
            'title_alias' => ['title', 'name'],
            'subtitle_alias' => ['subtitle', 'sub_title', 'slogan'],
            'summary_alias' => ['summary', 'describe', 'description', 'intro', 'remark'],
            'content_alias' => ['content', 'body', 'detail'],
            'cover_alias' => ['cover', 'image', 'thumb', 'pic', 'logo'],
            'gallery_alias' => ['images', 'gallery', 'photos', 'albums'],
            'video_alias' => ['video', 'video_url', 'media_url', 'video_src'],
            'file_alias' => ['file', 'file_url', 'attachment', 'download', 'download_url'],
            'date_alias' => ['date', 'publish_time', 'created_at', 'updated_at'],
            'pid_alias' => ['pid', 'parent_id'],
            'price_alias' => ['price', 'amount', 'money', 'sale_price', 'shop_price'],
            'origin_price_alias' => ['origin_price', 'market_price', 'original_price', 'old_price'],
            'stock_alias' => ['stock', 'inventory', 'quantity', 'stock_num'],
            'spec_alias' => ['spec', 'specification', 'specs', 'sku', 'model'],
            'tag_alias' => ['tag', 'tags', 'keyword', 'keywords', 'label'],
            'position_alias' => ['position', 'job', 'job_title', 'duty', 'title_name'],
            'phone_alias' => ['phone', 'tel', 'mobile'],
            'email_alias' => ['email', 'mail'],
            'address_alias' => ['address', 'company_address'],
            'link_alias' => ['url', 'link', 'href'],
        ];
    }

    public static function templates(): array
    {
        return [
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'list',
                'template_name' => '通用列表',
                'description' => '适合文章、公告、资讯、内容列表。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['summary_alias', 'date_alias', 'cover_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 10,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'cardGrid',
                'template_name' => '卡片网格',
                'description' => '适合案例、服务、产品、项目展示。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'summary_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 20,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'titleContentImage',
                'template_name' => '图文列表',
                'description' => '适合带简介、封面和正文摘要的栏目。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'content_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 30,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'titleImage',
                'template_name' => '标题配图',
                'description' => '适合轻图文栏目、单图专题和活动页。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'summary_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 40,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'treeList',
                'template_name' => '树形目录',
                'description' => '适合文档树、分类目录、帮助中心。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['pid_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 50,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'milestone',
                'template_name' => '发展历程',
                'description' => '适合时间线、历程、节点回顾。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['date_alias', 'content_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 60,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'photoList',
                'template_name' => '相册模板',
                'description' => '适合作品集、图库、图片内容。',
                'required_fields' => ['cover_alias'],
                'recommended_fields' => ['title_alias', 'summary_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 70,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'videoList',
                'template_name' => '视频列表',
                'description' => '适合视频内容、课程视频、作品视频。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['video_alias', 'cover_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 80,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'articleList',
                'template_name' => '资讯图文列表',
                'description' => '左封面右摘要的标准资讯列表，适合新闻、博客、公告。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'summary_alias', 'date_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 90,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'productGrid',
                'template_name' => '商品网格',
                'description' => '封面 + 价格 + 库存 + 标签，适合商品、课程、服务报价。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'price_alias', 'stock_alias', 'tag_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 100,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'productList',
                'template_name' => '商品列表',
                'description' => '左图右文的横向大卡，信息量比网格更大。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'price_alias', 'summary_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 110,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'galleryGrid',
                'template_name' => '相册瀑布流',
                'description' => 'CSS 多列瀑布 + 点击灯箱，适合作品集、图库、实景照片。',
                'required_fields' => ['cover_alias'],
                'recommended_fields' => ['gallery_alias', 'title_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 120,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'videoGrid',
                'template_name' => '视频网格',
                'description' => '封面带播放按钮，点击弹窗播放，支持 mp4 直链与主流平台。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['video_alias', 'cover_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 130,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'downloadList',
                'template_name' => '下载列表',
                'description' => '文件类型图标 + 说明 + 下载按钮，适合资料、手册、软件包。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['file_alias', 'summary_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 140,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'faqList',
                'template_name' => '问答折叠',
                'description' => '标题当问题、正文当答案，点击展开，适合 FAQ、帮助中心。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['content_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 150,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'teamGrid',
                'template_name' => '团队成员',
                'description' => '头像 + 姓名 + 职位 + 简介 + 联系方式，适合团队、讲师、专家。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'position_alias', 'summary_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 160,
            ],
            [
                'scene' => self::SCENE_LIST,
                'template_key' => 'priceTable',
                'template_name' => '价格套餐',
                'description' => '并排对比卡片，支持推荐套餐高亮，适合定价、会员等级。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['price_alias', 'origin_price_alias', 'spec_alias'],
                'supported_model_type' => ['multi'],
                'sort' => 170,
            ],
            [
                'scene' => self::SCENE_DETAIL,
                'template_key' => 'detail',
                'template_name' => '通用详情',
                'description' => '适合文章、公告、正文详情页。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['content_alias', 'date_alias', 'cover_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 10,
            ],
            [
                'scene' => self::SCENE_DETAIL,
                'template_key' => 'detailLeftList',
                'template_name' => '详情加左栏',
                'description' => '适合分类内容、目录型详情页。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['content_alias', 'pid_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 20,
            ],
            [
                'scene' => self::SCENE_DETAIL,
                'template_key' => 'detailRightList',
                'template_name' => '详情加右栏',
                'description' => '适合正文加推荐内容的详情结构。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['content_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 30,
            ],
            [
                'scene' => self::SCENE_DETAIL,
                'template_key' => 'detailBottomList',
                'template_name' => '详情加底栏',
                'description' => '适合正文后接推荐列表或扩展内容。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['content_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 40,
            ],
            [
                'scene' => self::SCENE_DETAIL,
                'template_key' => 'productDetail',
                'template_name' => '商品详情',
                'description' => '左侧图集 + 右侧价格规格咨询，适合商品、课程、套餐详情。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'gallery_alias', 'price_alias', 'content_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 50,
            ],
            [
                'scene' => self::SCENE_DETAIL,
                'template_key' => 'videoDetail',
                'template_name' => '视频详情',
                'description' => '顶部播放器 + 正文 + 相关推荐，适合视频、课程、直播回放。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['video_alias', 'cover_alias', 'content_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 60,
            ],
            [
                'scene' => self::SCENE_DETAIL,
                'template_key' => 'galleryDetail',
                'template_name' => '相册详情',
                'description' => '整页图集 + 灯箱放大，适合作品集、实景图、活动照片。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['gallery_alias', 'cover_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 70,
            ],
            [
                'scene' => self::SCENE_DETAIL,
                'template_key' => 'about',
                'template_name' => '关于我们',
                'description' => '单记录模型详情页，用于关于我们、公司介绍等单页内容。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['content_alias', 'cover_alias', 'gallery_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 80,
            ],
            [
                'scene' => self::SCENE_DETAIL,
                'template_key' => 'onlydetail',
                'template_name' => '纯详情',
                'description' => '只渲染单条记录正文，不带列表与侧栏，适合纯内容页。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['content_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 90,
            ],
            [
                'scene' => self::SCENE_DETAIL,
                'template_key' => 'carousel',
                'template_name' => '详情带轮播',
                'description' => '基于标题配图详情页，适合需要顶部轮播图的详情。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'gallery_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 100,
            ],
            [
                'scene' => self::SCENE_DETAIL,
                'template_key' => 'contacts',
                'template_name' => '联系方式',
                'description' => '单记录模型的字段自动罗列，适合联系方式、公司信息展示。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['phone_alias', 'email_alias', 'address_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 110,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'list',
                'template_name' => '首页列表区块',
                'description' => '适合首页内容列表、资讯区块、案例区块。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['summary_alias', 'cover_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 10,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'titleContentImage',
                'template_name' => '首页图文区块',
                'description' => '适合带导语、图文卡片的首页内容模块。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'content_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 20,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'titleImage',
                'template_name' => '首页标题配图区块',
                'description' => '适合品牌介绍、图片型专题区块。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'summary_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 30,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'milestone',
                'template_name' => '首页历程区块',
                'description' => '适合发展历程、时间轴、成长节点。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['date_alias', 'content_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 40,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'about',
                'template_name' => '首页关于我们区块',
                'description' => '适合公司简介、品牌故事、团队概况的单页内容。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'content_alias', 'gallery_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 45,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'contacts',
                'template_name' => '首页联系区块',
                'description' => '适合联系人、联系方式、公司信息展示。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['phone_alias', 'email_alias', 'address_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 50,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'feedback',
                'template_name' => '首页留言区块',
                'description' => '适合留言、反馈、互动入口。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['content_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 60,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'articleList',
                'template_name' => '首页资讯区块',
                'description' => '左封面右摘要的资讯列表，适合首页新闻、博客、公告。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'summary_alias', 'date_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 70,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'productGrid',
                'template_name' => '首页商品区块',
                'description' => '带价格与库存的商品卡片网格，适合首页推荐商品、热销课程。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'price_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 80,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'galleryGrid',
                'template_name' => '首页相册区块',
                'description' => '瀑布流图集 + 灯箱，适合首页作品集、实景展示。',
                'required_fields' => ['cover_alias'],
                'recommended_fields' => ['gallery_alias', 'title_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 90,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'videoGrid',
                'template_name' => '首页视频区块',
                'description' => '点击弹窗播放的视频卡片网格，适合首页课程视频、宣传片。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['video_alias', 'cover_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 100,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'teamGrid',
                'template_name' => '首页团队区块',
                'description' => '成员卡片网格，适合首页团队、讲师、专家介绍。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['cover_alias', 'position_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 110,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'downloadList',
                'template_name' => '首页下载区块',
                'description' => '带下载按钮的资料列表，适合首页资料、手册、软件包。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['file_alias', 'summary_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 120,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'faqList',
                'template_name' => '首页问答区块',
                'description' => '点击展开的问答折叠，适合首页常见问题、帮助入口。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['content_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 130,
            ],
            [
                'scene' => self::SCENE_HOME_SECTION,
                'template_key' => 'priceTable',
                'template_name' => '首页价格区块',
                'description' => '并排价格套餐卡片，适合首页定价、会员等级展示。',
                'required_fields' => ['title_alias'],
                'recommended_fields' => ['price_alias', 'spec_alias'],
                'supported_model_type' => ['single', 'multi'],
                'sort' => 140,
            ],
        ];
    }

    public static function templatesByScene(string $scene): array
    {
        return array_values(array_filter(self::templates(), static function (array $template) use ($scene) {
            return $template['scene'] === $scene;
        }));
    }

    /**
     * 根据模板 key 推断它属于哪个场景目录（list/detail/form）。
     *
     * 用于解析器在 model/overwrite/{scene}/{tpl} 子目录里查找模板。
     * list 与 detail 的 key 互不重叠；home_section 的 key 不会进这里。
     *
     * @return string 'list' | 'detail' | 'form' | ''
     */
    public static function sceneOf(string $key): string
    {
        $key = strtolower(trim($key));
        if ($key === '') {
            return '';
        }
        if ($key === 'handle') {
            return 'form';
        }

        static $detailKeys = null;
        static $listKeys = null;
        if ($detailKeys === null) {
            $detailKeys = array_flip(array_map('strtolower', array_column(self::templatesByScene(self::SCENE_DETAIL), 'template_key')));
            $listKeys = array_flip(array_map('strtolower', array_column(self::templatesByScene(self::SCENE_LIST), 'template_key')));
        }

        if (isset($detailKeys[$key])) {
            return self::SCENE_DETAIL;
        }
        if (isset($listKeys[$key])) {
            return self::SCENE_LIST;
        }

        return '';
    }

    /**
     * 可作为「列表模板」下拉选项的模板（后台 modelAdd/modelEdit 用）。
     * 单页型详情模板（about/onlydetail/carousel/contacts）也可以作为模型的列表页模板，
     * 故一并入选；其余按场景分组，分组名与目录结构一致（列表模板 / 单页·详情模板 / 首页区块·其它）。
     *
     * @return array ['自定义' => '', '列表模板' => [key=>name,...], '单页 / 详情模板' => [...], ...]
     */
    public static function listSelectOptions(): array
    {
        $groups = [];
        $seen = [];
        foreach (self::templates() as $t) {
            $key = $t['template_key'];
            if (isset($seen[$key])) {
                continue;
            }
            $scene = $t['scene'];
            $selectableAsList = $scene === self::SCENE_LIST
                || in_array($key, ['about', 'onlydetail', 'carousel', 'contacts', 'feedback'], true);
            if (!$selectableAsList) {
                continue;
            }
            $seen[$key] = true;
            $group = self::selectGroupLabel($scene, $key);
            $groups[$group][$key] = $t['template_name'];
        }

        $ordered = [];
        foreach (['列表模板', '单页 / 详情模板', '首页区块 / 其它'] as $g) {
            if (isset($groups[$g])) {
                $ordered[$g] = $groups[$g];
            }
        }
        foreach ($groups as $g => $opts) {
            if (!isset($ordered[$g])) {
                $ordered[$g] = $opts;
            }
        }

        return ['' => '自定义'] + $ordered;
    }

    /**
     * 可作为「详情模板」下拉选项的模板（后台 modelAdd/modelEdit 用）。
     *
     * @return array
     */
    public static function detailSelectOptions(): array
    {
        $groups = [];
        foreach (self::templates() as $t) {
            if ($t['scene'] !== self::SCENE_DETAIL) {
                continue;
            }
            $groups['详情模板'][$t['template_key']] = $t['template_name'];
        }

        return ['' => '自定义'] + $groups;
    }

    private static function selectGroupLabel(string $scene, string $key): string
    {
        if ($scene === self::SCENE_LIST) {
            return '列表模板';
        }
        if ($scene === self::SCENE_DETAIL) {
            return '单页 / 详情模板';
        }
        return '首页区块 / 其它';
    }

    public static function defaultHomeSectionSchema(?string $accessIdentification = null, ?string $title = null, ?string $description = null): string
    {
        $accessIdentification = trim((string) $accessIdentification);
        $title = trim((string) $title);
        $description = trim((string) $description);

        $schema = [
            'theme' => [
                'primary' => '#2563eb',
                'accent' => '#0f172a',
                'surface' => '#ffffff',
                'radius' => '20px',
                'shadow' => '0 16px 34px rgba(15, 23, 42, 0.08)',
            ],
            'sections' => [
                [
                    'type' => 'section',
                    'props' => [
                        'contentWidth' => 'contained',
                        'innerWidth' => '1200px',
                        'anchor' => 'home-' . ($accessIdentification !== '' ? $accessIdentification : 'module'),
                    ],
                    'style' => [
                        'padding' => '72px 0',
                        'background' => 'linear-gradient(180deg, #f8fbff 0%, #ffffff 100%)',
                        'borderRadius' => '28px',
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'style' => [
                                'gap' => '24px',
                                'alignItems' => 'center',
                            ],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'props' => [
                                        'span' => 8,
                                    ],
                                    'blocks' => [
                                        [
                                            'type' => 'text',
                                            'props' => [
                                                'text' => 'HOME MODULE',
                                                'className' => 'mx-page-eyebrow',
                                            ],
                                            'style' => [
                                                'margin' => '0 0 10px',
                                                'color' => '#2563eb',
                                                'fontWeight' => '700',
                                                'letterSpacing' => '0.18em',
                                            ],
                                        ],
                                        [
                                            'type' => 'heading',
                                            'props' => [
                                                'level' => 'h2',
                                                'text' => $title !== '' ? $title : '首页推荐区块标题',
                                            ],
                                            'style' => [
                                                'margin' => '0 0 12px',
                                                'color' => '#0f172a',
                                            ],
                                        ],
                                        [
                                            'type' => 'text',
                                            'props' => [
                                                'text' => $description !== '' ? $description : '这里可以继续放模型说明、品牌文案、首页区块导语和转化引导。',
                                            ],
                                            'style' => [
                                                'margin' => '0 0 20px',
                                                'color' => '#64748b',
                                                'lineHeight' => '1.9',
                                            ],
                                        ],
                                        [
                                            'type' => 'button',
                                            'props' => [
                                                'href' => $accessIdentification !== '' ? '/list/' . $accessIdentification : '#',
                                                'text' => '查看全部',
                                                'align' => 'left',
                                                'variant' => 'solid',
                                            ],
                                            'style' => [
                                                'display' => 'inline-flex',
                                                'padding' => '12px 22px',
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'column',
                                    'props' => [
                                        'span' => 4,
                                    ],
                                    'blocks' => [
                                        [
                                            'type' => 'stats',
                                            'props' => [
                                                'title' => '亮点数据',
                                                'intro' => '首页聚合区块建议配一组高对比度数据。',
                                                'columns' => 2,
                                                'items' => [
                                                    [
                                                        'label' => '推荐数量',
                                                        'value' => '06',
                                                        'suffix' => '',
                                                        'description' => '首页展示条目',
                                                    ],
                                                    [
                                                        'label' => '更新频率',
                                                        'value' => '24',
                                                        'suffix' => 'H',
                                                        'description' => '内容持续更新',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'model_list',
                            'props' => [
                                'model' => $accessIdentification !== '' ? $accessIdentification : 'news',
                                'limit' => 6,
                                'template' => 'card',
                                'title' => '',
                            ],
                            'style' => [
                                'margin' => '28px 0 0',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public static function defaultSceneSchema(string $scene, ?string $accessIdentification = null, ?string $title = null, ?string $description = null): string
    {
        return match ($scene) {
            self::SCENE_LIST => self::defaultListSceneSchema($accessIdentification, $title, $description),
            self::SCENE_DETAIL => self::defaultDetailSceneSchema($accessIdentification, $title, $description),
            default => self::defaultHomeSectionSchema($accessIdentification, $title, $description),
        };
    }

    public static function defaultListSceneSchema(?string $accessIdentification = null, ?string $title = null, ?string $description = null): string
    {
        $accessIdentification = trim((string) $accessIdentification);
        $title = trim((string) $title);
        $description = trim((string) $description);
        $schema = [
            'theme' => [
                'primary' => '#2563eb',
                'accent' => '#0f172a',
                'surface' => '#ffffff',
                'radius' => '20px',
                'shadow' => '0 16px 34px rgba(15, 23, 42, 0.08)',
            ],
            'sections' => [
                [
                    'type' => 'section',
                    'props' => [
                        'contentWidth' => 'contained',
                        'innerWidth' => '1200px',
                        'anchor' => 'list-hero',
                    ],
                    'style' => [
                        'padding' => '64px 0 26px',
                        'background' => 'linear-gradient(180deg, #f8fbff 0%, #ffffff 100%)',
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'style' => [
                                'gap' => '26px',
                                'alignItems' => 'center',
                            ],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'props' => [
                                        'span' => 8,
                                    ],
                                    'blocks' => [
                                        [
                                            'type' => 'text',
                                            'props' => [
                                                'text' => 'LIST LANDING',
                                            ],
                                            'style' => [
                                                'margin' => '0 0 12px',
                                                'color' => '#2563eb',
                                                'fontWeight' => '700',
                                                'letterSpacing' => '0.18em',
                                            ],
                                        ],
                                        [
                                            'type' => 'heading',
                                            'props' => [
                                                'level' => 'h1',
                                                'text' => $title !== '' ? $title : '商业化列表页标题',
                                            ],
                                            'style' => [
                                                'margin' => '0 0 14px',
                                                'color' => '#0f172a',
                                            ],
                                        ],
                                        [
                                            'type' => 'text',
                                            'props' => [
                                                'text' => $description !== '' ? $description : '这里是列表页导语区，可用于介绍栏目定位、筛选说明和转化引导，下面的模型列表会自动承接当前分页结果。',
                                            ],
                                            'style' => [
                                                'margin' => '0 0 22px',
                                                'color' => '#64748b',
                                                'lineHeight' => '1.9',
                                            ],
                                        ],
                                        [
                                            'type' => 'button',
                                            'props' => [
                                                'href' => '#list-content',
                                                'text' => '浏览内容',
                                                'align' => 'left',
                                                'variant' => 'solid',
                                            ],
                                            'style' => [
                                                'display' => 'inline-flex',
                                                'padding' => '12px 22px',
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'column',
                                    'props' => [
                                        'span' => 4,
                                    ],
                                    'blocks' => [
                                        [
                                            'type' => 'cta',
                                            'props' => [
                                                'eyebrow' => 'Quick Brief',
                                                'title' => '列表页也应该带转化入口',
                                                'description' => '把筛选说明、服务优势、联系方式或下载入口直接放进列表页首屏。',
                                                'primaryText' => '联系顾问',
                                                'primaryHref' => '/contact',
                                                'secondaryText' => '查看案例',
                                                'secondaryHref' => '/cases',
                                                'align' => 'left',
                                            ],
                                            'style' => [
                                                'padding' => '24px',
                                                'borderRadius' => '24px',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'props' => [
                        'contentWidth' => 'contained',
                        'innerWidth' => '1200px',
                        'anchor' => 'list-content',
                    ],
                    'style' => [
                        'padding' => '18px 0 64px',
                        'background' => '#ffffff',
                    ],
                    'children' => [
                        [
                            'type' => 'model_list',
                            'props' => [
                                'model' => $accessIdentification !== '' ? $accessIdentification : 'news',
                                'limit' => 12,
                                'template' => 'card',
                                'title' => '内容列表',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public static function defaultDetailSceneSchema(?string $accessIdentification = null, ?string $title = null, ?string $description = null): string
    {
        $accessIdentification = trim((string) $accessIdentification);
        $title = trim((string) $title);
        $description = trim((string) $description);
        $schema = [
            'theme' => [
                'primary' => '#2563eb',
                'accent' => '#0f172a',
                'surface' => '#ffffff',
                'radius' => '20px',
                'shadow' => '0 16px 34px rgba(15, 23, 42, 0.08)',
            ],
            'sections' => [
                [
                    'type' => 'section',
                    'props' => [
                        'contentWidth' => 'contained',
                        'innerWidth' => '1200px',
                        'anchor' => 'detail-content',
                    ],
                    'style' => [
                        'padding' => '64px 0',
                        'background' => 'linear-gradient(180deg, #ffffff 0%, #f8fafc 100%)',
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'style' => [
                                'gap' => '28px',
                                'alignItems' => 'start',
                            ],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'props' => [
                                        'span' => 8,
                                    ],
                                    'blocks' => [
                                        [
                                            'type' => 'text',
                                            'props' => [
                                                'text' => $description !== '' ? $description : '详情页可视化模式适合重做头图、正文、摘要、推荐区和行动区，下面直接承接当前详情数据。',
                                            ],
                                            'style' => [
                                                'margin' => '0 0 18px',
                                                'color' => '#64748b',
                                                'lineHeight' => '1.85',
                                            ],
                                        ],
                                        [
                                            'type' => 'model_detail',
                                            'props' => [
                                                'model' => $accessIdentification !== '' ? $accessIdentification : 'news',
                                                'template' => 'detail',
                                                'title' => $title !== '' ? $title : '',
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'column',
                                    'props' => [
                                        'span' => 4,
                                    ],
                                    'blocks' => [
                                        [
                                            'type' => 'cta',
                                            'props' => [
                                                'eyebrow' => 'DETAIL SIDEBAR',
                                                'title' => '把详情页做成可转化的商业页',
                                                'description' => '这里适合放咨询入口、附件下载、服务亮点、相关推荐或联系方式。',
                                                'primaryText' => '立即咨询',
                                                'primaryHref' => '/contact',
                                                'secondaryText' => '返回列表',
                                                'secondaryHref' => $accessIdentification !== '' ? '/list/' . $accessIdentification : '#',
                                                'align' => 'left',
                                            ],
                                            'style' => [
                                                'padding' => '24px',
                                                'borderRadius' => '24px',
                                            ],
                                        ],
                                        [
                                            'type' => 'qrcode',
                                            'props' => [
                                                'title' => '扫码获取方案',
                                                'text' => '详情页右侧可以直接挂二维码、顾问卡片或下载入口。',
                                                'value' => 'https://example.com/contact',
                                                'size' => '128',
                                            ],
                                            'style' => [
                                                'margin' => '18px 0 0',
                                                'padding' => '20px',
                                                'background' => '#ffffff',
                                                'borderRadius' => '20px',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
