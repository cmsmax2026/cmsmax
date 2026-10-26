<?php

namespace Modules\Formtools\Helper;


trait FormFunc {

    //表单类型
    public static function formtype() {
        return [
            'text' => '单行文本',
            'textarea' => '多行文本',
            'json' => 'JSON 配置',
            'code' => '代码块',
            'tags' => '标签输入',
            'editor' => '编辑器',
            'password' => '密码输入',
            'radio' => '单项选框',
            'checkbox' => '复项选框',
            'checkboxList' => '复选列表',
            'select' => '下拉列表',
            'selectMore' => '下拉多选',
            'multipleSelect' => '增强多选',
            'number' => '数字输入',
            'color' => '颜色选择器',
            'upload' => '上传文件【同步】',
            'uploadAjax' => '上传文件【异步】',
            'image' => '上传图片【同步】',
            'imageAjax' => '上传图片【异步】',
            'date' => '日期选择',
            'dateMonth' => '月份选择',
            'dateYear' => '年份选择',
            'time' => '时间选择',
            'datetime' => '日期时间',
            'switch' => '开关切换',
            'hidden' => '隐藏域',
            'readonly' => '只读文本',
            'disabled' => '禁用文本',
            'word' => '单词输入',
            'links' => '链接列表',
            'legend' => '标题说明',
            'button' => '按钮组',
            'section' => '分组标题',
        ];

    }

    //字段类型
    public static function fieldtype() {
        return [
            'string' => 'varchar(0-65525)',
            'char' => 'char(0-225)',
            'integer' => 'int(11)',
            'tinyInteger' => 'tinyint(4)',
            'text' => 'text(unlimit)',
            'tinyText' => 'tinytext(225)',
            'mediumText' => 'mediumtext(16777215)',
            'longText' => 'longtext(4294967295)',
            'float' => 'float(8,2)',
            'double' => 'double(16,2)',
            'decimal' => 'decimal(10,2)',
            'boolean' => 'boolean(0-1)',
            'enum' => 'enum(0-255)',
            'json' => 'json(unlimit)',
            'jsonb' => 'jsonb(unlimit)',
            'year' => 'year(YYYY)',
            'date' => 'date(YYYY-MM-DD)',
            'dateTime' => 'dateTime(YYYY-MM-DD HH:MM:SS)',
            'timestamp' => 'timestamp(YYYY-MM-DD HH:MM:SS)',
            'time' => 'time(HH:MM:SS)',
            'binary' => 'binary(0-255)',
            'varbinary' => 'varbinary(0-65535)',
            'bit' => 'bit(0-64)',
            'ipAddress' => 'ipAddress(0-255)',
            'macAddress' => 'macAddress(0-255)',
            'blob' => 'blob(unlimit)',
            'tinyBlob' => 'tinyblob(255)',
            'mediumBlob' => 'mediumblob(16777215)',
            'longBlob' => 'longblob(4294967295)',
        ];
    }

    //是否是索引
    public static function isindex() {
        return [
            'NOINDEX' => '不设索引',
            'INDEX' => '普通索引',
            'UNIQUE' => '唯一索引',
            'FULLTEXT' => '全文索引',
        ];
    }

    //字段规则
    public static function rule() {
        return [
            'unlimited' => '不限制',
            'string' => '字符类型（不限制输入）',
            'unique' => '唯一型（不可重复，比对数据库）',
            'email' => '邮箱类型',
            'phone' => '手机号码',
            'number' => '数字类型',
            'date' => '日期类型',
            'time' => '时间类型',
            'scope' => '范围类型（时间范围，前后不能矛盾）',
            'file' => '文件类型（文件上传，PDF、MP3、MP4、word文档、txt）',
            'image' => '图片类型（图片上传，png、jpeg、gif、ico）',
            'url' => '链接类型',
            'ip' => 'IP类型',
            'idcard' => '身份证类型',
            'zip' => '邮编类型',
            'regex' => '正则表达式',
        ];
    }

    //列表模板（下拉选项，来源统一为 FormTemplateCatalog，避免与模板文件/目录脱节）
    public static function listTemplate() {
        return \Modules\Formtools\Support\FormTemplateCatalog::listSelectOptions();
    }

    //详情模板（下拉选项，来源统一为 FormTemplateCatalog）
    public static function detailTemplate() {
        return \Modules\Formtools\Support\FormTemplateCatalog::detailSelectOptions();
    }

    /**
     * 列表 / 详情模板的联动规则
     *  - implies_type: 选中后自动设置的 type（multi 单选列表 / single 单页）；any 表示不动
     *  - implies_doc_mode: 选中后自动设置的 front_doc_mode（on / off）；any 表示不动
     *  - incompatible_detail: 选了这个列表模板时，不允许的详情模板 key
     *  - requires_list: 详情模板专用 - 选了之后需要列表模板（与 onlydetail 互斥）
     */
    public static function templateRules() {
        return [
            'list' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'cardGrid' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'treeList' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'on',
                'incompatible_detail' => [],
            ],
            'titleContentImage' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'titleImage' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'contacts' => [
                'implies_type' => 'single',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => ['detailLeftList', 'detailRightList', 'detailBottomList'],
            ],
            'about' => [
                'implies_type' => 'single',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => ['detailLeftList', 'detailRightList', 'detailBottomList'],
            ],
            'milestone' => [
                'implies_type' => 'single',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => ['detailLeftList', 'detailRightList', 'detailBottomList'],
            ],
            'feedback' => [
                'implies_type' => 'single',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => ['detailLeftList', 'detailRightList', 'detailBottomList'],
            ],
            'carousel' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'photoList' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'videoList' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'onlydetail' => [
                'implies_type' => 'single',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => ['detailLeftList', 'detailRightList', 'detailBottomList'],
            ],
            'articleList' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'productGrid' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'productList' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'galleryGrid' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'videoGrid' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'downloadList' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'faqList' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'teamGrid' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
            'priceTable' => [
                'implies_type' => 'multi',
                'implies_doc_mode' => 'off',
                'incompatible_detail' => [],
            ],
        ];
    }

    /**
     * 详情模板侧栏/底栏系列 - 依赖列表模板非 onlydetail
     */
    public static function detailRequiresListKeys() {
        return ['detailLeftList', 'detailRightList', 'detailBottomList'];
    }
}
