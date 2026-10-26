<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\System\Models\Attachments;
use Modules\System\Services\ServiceModel;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/5/16
 * Time: 17:50
 */

function package_root_relative(string $type = 'module'): string {
    switch (strtolower($type)) {
        case 'plugin':
            $candidates = ['plugins', 'Plugins'];
            break;
        case 'module':
            $candidates = ['modules', 'Modules'];
            break;
        default:
            $candidates = [strtolower($type) . 's', ucfirst(strtolower($type)) . 's'];
            break;
    }

    foreach ($candidates as $candidate) {
        if (is_dir(base_path($candidate))) {
            return $candidate;
        }
    }

    return $candidates[0] ?? ($candidates[1] ?? '');
}

function package_root_path(string $type = 'module', string $path = ''): string {
    $root = base_path(package_root_relative($type));
    if ($path === '') {
        return $root;
    }

    return rtrim($root, '\\/') . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
}

function package_directory_name(string $name, string $type = 'module'): string {
    $name = trim($name);
    if ($name === '') {
        return '';
    }

    $root = package_root_path($type);
    if (is_dir($root)) {
        foreach (scandir($root) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (strcasecmp($entry, $name) === 0) {
                return $entry;
            }
        }

        foreach (scandir($root) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $configFile = rtrim($root, '\\/') . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'config.php';
            if (!is_file($configFile)) {
                continue;
            }

            $configContent = file_get_contents($configFile);
            if ($configContent === false || $configContent === '') {
                continue;
            }

            preg_match("/['\"]identification['\"]\s*=>\s*['\"]([^'\"]+)['\"]/", $configContent, $identificationMatches);
            preg_match("/['\"]directory['\"]\s*=>\s*['\"]([^'\"]+)['\"]/", $configContent, $directoryMatches);
            $identification = trim((string) ($identificationMatches[1] ?? ''));
            $directory = trim((string) ($directoryMatches[1] ?? ''));
            if (($identification !== '' && strcasecmp($identification, $name) === 0)
                || ($directory !== '' && strcasecmp($directory, $name) === 0)) {
                return $entry;
            }
        }
    }

    $candidates = array_values(array_unique([
        $name,
        strtolower($name),
        ucfirst($name),
    ]));

    foreach ($candidates as $candidate) {
        if (is_dir($root . DIRECTORY_SEPARATOR . $candidate)) {
            return $candidate;
        }
    }

    return $name;
}

function package_relative_path(string $type = 'module', string $path = ''): string {
    $root = package_root_relative($type);
    if ($path === '') {
        return $root;
    }

    return rtrim($root, '\\/') . '/' . ltrim($path, '\\/');
}

function modules_base_path(string $path = ''): string {
    return package_root_path('module', $path);
}

function plugins_base_path(string $path = ''): string {
    return package_root_path('plugin', $path);
}

function modules_relative_path(string $path = ''): string {
    return package_relative_path('module', $path);
}

function plugins_relative_path(string $path = ''): string {
    return package_relative_path('plugin', $path);
}

function module_path($name, $path = '', $type = "Module") {
    if ($type == 'theme') {
        return public_path('views/themes/' . $name . '/' . $path);
    }

    $directory = package_directory_name((string) $name, $type);
    return package_root_path($type, trim($directory . '/' . $path, '/'));
}

//file文件
function modifyEnv(array $data) {
    $envPath = base_path() . DIRECTORY_SEPARATOR . '.env';

    $contentArray = collect(file($envPath, FILE_IGNORE_NEW_LINES));

    $contentArray->transform(function ($item) use ($data) {
        foreach ($data as $key => $value) {
            if (str_contains($item, $key)) {
                return $key . '=' . $value;
            }
        }
        return $item;
    });

    foreach ($data as $key => $value) {
        $is_key = false;
        foreach ($contentArray->toArray() as $content) {
            if (str_contains($content,$key)) {
                $is_key = true;
                 break;
            }
        }
        if (!$is_key) {
            $contentArray->push($key."=".$value);
        }
    }
    $content = implode("\n", $contentArray->toArray());

    File::put($envPath, $content);
}


//上传单文件
function UploadFile($request,
                    $field,
                    $filename,
                    $allowExt = ALLOWEXT,
                    $drive = "local",
                    $preview = null,
                    $preview_w = 0,
                    $preview_h = 0,
                    $watermark_type = null,
                    $watermark_text = "",
                    $preview_watermark = null,
                    $watermark = null,
                    $autoProcess = true
) {

    //判断是否开启文件上传功能
    if (__E("upload_status") == 0) {
        throw new Exception("系统没有开启上传功能！", 40000);
    }

    //格式白名单：调用方传系统默认 ALLOWEXT（或留空）时，以后台"上传格式"设置为准
    $formatSetting = trim((string) __E("upload_format"));
    if ($formatSetting !== '' && (trim((string) $allowExt) === '' || $allowExt === ALLOWEXT)) {
        $allowExt = $formatSetting;
    }
    $allowExt = strtolower(trim((string) $allowExt) ?: ALLOWEXT);

    //判断文件大小
    if ($_FILES[$field]['size'] <= 0) {

        throw new Exception("文件没有选择！", 40000);

    } else {

        //判断文件大小
        if ($_FILES[$field]['size'] / 1024 > __E("upload_limit")) {
            throw new Exception("超出文件设置大小！", 40000);
        }

        //判断文件格式
        $file = $request->file($field);
        //获取文件的扩展名
        $originalName = $file ? (string) $file->getClientOriginalName() : '';
        $ext = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        if (!$ext) {
            $ext = strtolower((string) $file->getClientOriginalExtension());
        }
        if (!$ext && method_exists($file, 'guessExtension')) {
            $ext = strtolower((string) $file->guessExtension());
        }
        if (!$ext) {
            $mimeType = $file->getClientMimeType();
            $mimeType = explode("/", (string) $mimeType);
            $ext = strtolower((string) ($mimeType[1] ?? ''));
        }
        //过滤文件格式
        if (!in_array($ext, explode(",", $allowExt))) {
            throw new Exception("文件格式不允许！", 40000);
        }
    }

    switch (__E("upload_driver")) {

        case "local":

            return _upload($request, $field, $filename, $allowExt, $drive, true, $preview, $preview_w, $preview_h, $watermark_type, $watermark_text, $preview_watermark, $watermark, $autoProcess);

            break;


        default :

            //判断插件是否存在
            if (is_dir(plugins_base_path(__E("upload_driver")))) {

                //触发上传驱动事件

                $filename = hook("Upload",[
                    "request"=>$request,
                    "field"=>$field,
                    "filename"=>$filename,
                    "allowExt"=>$allowExt,
                    "drive"=>__E("upload_driver"),
                    "moduleName"=>__E("upload_driver"),
                    "cloudType"=>"plugin"
                ]);

                //event(new \App\Events\UploadDriver($request, $field, $filename, $allowExt, $drive, "put"));

                return $filename[0];

            } else {

                throw new Exception("Upload driver does not exist！", 40000);

            }

            break;


    }


}

//上传操作
function _upload($request,
                 $field,
                 $filename,
                 $allowExt,
                 $drive,
                 $addAttachment = true,
                 $preview = null,
                 $preview_w = 0,
                 $preview_h = 0,
                 $watermark_type = null,
                 $watermark_text = "",
                 $preview_watermark = null,
                 $watermark = null,
                 $autoProcess = true
) {
    if ($_FILES[$field]['size'] <= 0) {
        return false;
    }
    $file = $request->file($field);
    if ($file->isValid()) { //括号里面的是必须加的哦
        //如果括号里面的不加上的话，下面的方法也无法调用的
        //获取文件的扩展名
        $originalName = $file ? (string) $file->getClientOriginalName() : '';
        $ext = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        if (!$ext) {
            $ext = strtolower((string) $file->getClientOriginalExtension());
        }
        if (!$ext && method_exists($file, 'guessExtension')) {
            $ext = strtolower((string) $file->guessExtension());
        }
        if (!$ext) {
            $mimeType = $file->getClientMimeType();
            $mimeType = explode("/", (string) $mimeType);
            $ext = strtolower((string) ($mimeType[1] ?? ''));
        }
        //获取文件的绝对路径
        $path = $file->getRealPath();

        //定义文件名
        $filename = $filename . '.' . $ext;

        //存储文件。disk里面的public。总的来说，就是调用disk模块里的public配置
        Storage::disk($drive)->put($filename, file_get_contents($path));

        //图片统一处理：原图水印 + 自动缩略图（读取后台"上传设置"，$autoProcess=false 时跳过）
        $sourcePath = public_path() . "/" . 'uploads' . "/" . $filename;
        $pre_filename = null;

        if ($autoProcess && isImageExt($ext) && is_file($sourcePath)) {

            //原图水印：调用方显式传真值强制执行；未传(null)时按后台"水印设置"自动执行；传 false 明确跳过
            $formatList = array_values(array_filter(array_map('trim', explode(',', strtolower((string) __E("watermark_upload_format"))))));
            $watermarkApplies = $formatList ? in_array($ext, $formatList, true) : in_array($ext, ['jpg', 'jpeg', 'png'], true);

            if ($watermark || ($watermark === null && $watermarkApplies)) {
                try {
                    $originImage = \Intervention\Image\ImageManager::gd()->read($sourcePath);
                    if (applyImageWatermark($originImage, $watermark_type, $watermark_text)) {
                        $originImage->save($sourcePath);
                    }
                } catch (Exception $exception) {
                    //水印失败不影响文件本身
                }
            }

            //自动缩略图（后台"上传设置-缩略图"控制，保存到 uploads/thumb/）
            $thumbPath = makeUploadThumb($sourcePath, $filename);
            if ($thumbPath && $addAttachment) {
                addAttachment($thumbPath, $drive);
            }
        }

        if ($preview) {
            //定义预览名称,设置裁剪图片保存的名称
            $pre_filename = public_path() . "/" . 'uploads' . "/" . "preview" . "/" . $filename;
            if (!is_dir(dirname($pre_filename))) {
                mk_dir(dirname($pre_filename), 0777);
            }
            try {
                //预览图
                $image = \Intervention\Image\ImageManager::gd()->read($sourcePath);
                // intervention/image 3.x：等比缩放用 scale()，单一维度传 null 即 2.x 的 widen/heighten
                if ($preview_w && $preview_h) {
                    $image->scale($preview_w, $preview_h);
                } else if ($preview_w) {
                    $image->scale($preview_w, null);
                } else if ($preview_h) {
                    $image->scale(null, $preview_h);
                }
                if ($preview_watermark) {
                    applyImageWatermark($image, $watermark_type, $watermark_text);
                }
                $image->save($pre_filename);

            } catch (Exception $exception) {
                throw new \Exception($exception->getMessage());
            }
        }

        //添加到附件表
        if ($addAttachment) {
            addAttachment($filename, $drive);
            if ($pre_filename) {
                addAttachment("preview" . "/" . $filename, $drive);
            }
        }

        return $filename;
    }
}


//判断是否为图片扩展名
function isImageExt($ext) {
    return in_array(strtolower((string) $ext), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
}

//水印字体文件：优先项目内置，其次本机系统字体；找不到返回空串（跳过文字水印）
function watermarkFontPath() {
    $candidates = [
        public_path('fonts/watermark.ttf'),
        public_path('xkb.ttf'),
    ];
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        foreach (['simhei.ttf', 'msyh.ttc', 'arial.ttf'] as $font) {
            $candidates[] = 'C:\\Windows\\Fonts\\' . $font;
        }
    }
    foreach ($candidates as $file) {
        if (is_file($file)) {
            return $file;
        }
    }
    return '';
}

//水印图片的绝对路径（本地磁盘存储），支持完整 URL
function watermarkImgAbsolutePath() {
    $path = trim((string) __E("watermark_img"));
    if ($path === '') {
        return '';
    }
    if (stripos($path, 'http') === 0) {
        return $path;
    }
    return public_path('uploads/' . ltrim(str_replace('\\', '/', $path), '/'));
}

//按后台"上传设置"给 Intervention Image 实例打水印；调用方可显式指定类型和文字
function applyImageWatermark($image, $watermarkType = null, $watermarkText = "") {
    if ($watermarkType === false) {
        return false;
    }
    if ($watermarkType === null || $watermarkType === '') {
        $watermarkType = __E("watermark_type");
    }
    if (!in_array($watermarkType, ["img", "text"], true)) {
        return false;
    }

    try {
        if ($watermarkType === "img") {
            $imgPath = watermarkImgAbsolutePath();
            if ($imgPath === '' || !is_file($imgPath)) {
                return false;
            }
            $image->place($imgPath, __E("watermark_position") ?: 'bottom-right', 10, 10);
        } else {
            $text = $watermarkText !== "" ? $watermarkText : (string) __E("watermark_text");
            if ($text === '') {
                return false;
            }
            $fontFile = watermarkFontPath();
            if ($fontFile === '') {
                return false;
            }
            // intervention/image 3.x：回调收到的是 FontFactory（链式 file/size/color/valign/angle）
            $image->text($text, 20, 20, function (\Intervention\Image\Typography\FontFactory $font) use ($fontFile) {
                $font->file($fontFile);
                $font->size((int) (__E("watermark_text_size") ?: 19));
                $font->color(__E("watermark_text_color") ?: '#000000');
                $font->valign('top');
                $font->angle((int) (__E("watermark_text_angle") ?: 0));
            });
        }
        return true;
    } catch (Exception $exception) {
        return false;
    }
}

//按后台"上传设置"生成自动缩略图，保存到 uploads/thumb/<filename>，返回附件相对路径或 null
function makeUploadThumb($sourceAbsolutePath, $filename) {
    if (__E("thumb_auto") != 1 || !isImageExt(pathinfo($filename, PATHINFO_EXTENSION))) {
        return null;
    }

    $width = max(10, (int) (__E("thumb_width") ?: 400));
    $height = max(10, (int) (__E("thumb_height") ?: 400));
    $method = __E("thumb_method") ?: 'message';

    try {
        $thumb = \Intervention\Image\ImageManager::gd()->read($sourceAbsolutePath);

        if ($method === "draw") {
            //等比缩放：跟随原图比例，整体缩放进最大宽高框内（不变形、不留白、不裁剪）
            $thumb->scale($width, $height);
        } else if ($method === "cut") {
            //裁减：等比缩放后居中裁剪（3.x 的 cover 等价于 2.x 的 fit）
            $thumb->cover($width, $height);
        } else {
            //留白：等比缩放放进白底画布
            $thumb->scale($width, $height);
            $canvas = \Intervention\Image\ImageManager::gd()->create($width, $height)->fill('#ffffff');
            $canvas->place($thumb, 'center');
            $thumb = $canvas;
        }

        $thumbPath = public_path('uploads/thumb/' . $filename);
        $thumbDir = dirname($thumbPath);
        if (!is_dir($thumbDir)) {
            mk_dir($thumbDir, 0777);
        }
        $thumb->save($thumbPath);

        return 'thumb/' . $filename;
    } catch (Exception $exception) {
        //缩略图失败不影响原图上传
        return null;
    }
}

//添加附件表
function addAttachment($filename, $drive) {

    $arr["path"] = $filename;
    $arr["drive"] = $drive;

    $Attachment = new ServiceModel();

    return $Attachment->InsertArr($arr);


}

//删除附件
function delAttachment($filename) {

    $arr["path"] = $filename;

    $Attachment = new ServiceModel();

    return $Attachment->deleteByPathMD5($arr["path"]);

}

//获取本地存储的文件
function GetLocalFileByPath($path) {
    return asset('uploads') . '/' . $path;
}

//获取驱动的URL
function GetUrlByPath($path) {
    if (!$path) return $path;
    if (strpos($path, 'http') !== false) return $path;
    $data = ServiceModel::getByPath($path);
    if ($data) {
        switch ($data["drive"]) {
            case "local":
                return GetLocalFileByPath($path);
                break;
            default :
                return $path;
        }
    } else {
        return GetLocalFileByPath($path);
    }
}

if (!function_exists('write_lock_file')) {
    /**
     * 写入锁文件
     * @param $path
     */
    function write_lock_file($path, $content = '', $file_name = 'lock') {
        $lock_file = fopen($path . '/' . $file_name, 'w+');//创建 锁文件
        fwrite($lock_file, empty($content) ? date('Y-m-d H:i:s') : $content);//写入
    }
}

if (!function_exists('get_dir_files')) {
    // 列出指定目录下所有目录和文件
    function get_dir_files($dir, &$arr = []) {
        if (is_dir($dir)) {//如果是目录，则进行下一步操作
            $d = opendir($dir);//打开目录
            if ($d) {//目录打开正常
                while (($file = readdir($d)) !== false) {//循环读出目录下的文件，直到读不到为止
                    if ($file != '.' && $file != '..') {//排除一个点和两个点
                        if (is_dir($dir . '/' . $file)) {//如果当前是目录
                            get_dir_files($dir . '/' . $file, $arr);//进一步获取该目录里的文件
                        } else {
                            $arr[] = $dir . '/' . $file;//记录文件名
                        }
                    }
                }
            }
            closedir($d);//关闭句柄
        }
//        return $arr;
    }
}

if (!function_exists('get_file_filtering')) {
    /**
     * 获取指定格式的文件
     * @param array $array
     * @param array $format
     * @return array
     */
    function get_file_filtering($array = [], $format = []) {
        $return = [];
        if (empty($array) || empty($format)) return $return;
        foreach ($array as $key => $value) {
            $arr = pathinfo($value);
            if (!empty($arr['extension']) && in_array($arr['extension'], $format)) $return[] = $value;
        }
        return $return;
    }
}

if (!function_exists('write_lock_file')) {
    /**
     * 写入锁文件
     * @param $path
     */
    function write_lock_file($path, $content = '', $file_name = 'lock') {
        $lock_file = fopen($path . '/' . $file_name, 'w+');//创建 锁文件
        fwrite($lock_file, empty($content) ? date('Y-m-d H:i:s') : $content);//写入
    }
}

if (!function_exists('del_dir_files')) {
    /**
     * 删除文件夹与下方的所有文件
     * @param $dirName 文件夹名称
     * @param int $delete_dir 是否删除文件夹【1.删除；0.不删除】
     */
    function del_dir_files($dirName, $delete_dir = 1) {
        if ($handle = @opendir($dirName)) {
            while (false !== ($item = @readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    if (is_dir($dirName . '/' . $item)) del_dir_files($dirName . '/' . $item);
                    else @unlink($dirName . '/' . $item);
                }
            }
            @closedir($handle);
        }
        if ($delete_dir == 1) @rmdir($dirName);
    }
}


if (!function_exists('put_file_to_zip')) {
    /**
     * 把指定文件目录下的所有文件，打包压缩至压缩包内
     * @param $path
     * @param $zip
     * @param $old_filename
     * @param $limit_dir 限制压缩的文件目录
     */
    function put_file_to_zip($path, $zip, $old_filename, $limit_dir = []) {
        $handler = opendir($path); //打开当前文件夹由$path指定。
        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") {//文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if (is_dir($path . "/" . $filename)) {// 如果读取的某个对象是文件夹，则递归
                    if (!empty($limit_dir) && !in_array($filename, $limit_dir)) continue;
                    $old_filename = (empty($old_filename) ? '' : ($old_filename . '/'));
                    $zip->addEmptyDir($old_filename . $filename);
                    put_file_to_zip($path . "/" . $filename, $zip, $old_filename . $filename);
                } else { //将文件加入zip对象
                    $zip->addFile($path . "/" . $filename, (empty($old_filename) ? '' : ($old_filename . '/')) . $filename);
                }
            }
        }
        @closedir($path);
    }
}

if (!function_exists('get_dir_size')) {
    /**
     * 获取指定文件夹的大小
     * @param $path
     * @param int $fileseze
     * @param array $limit_dir
     * @return int
     */
    function get_dir_size($path, $fileseze = 0, $limit_dir = []) {
        header("content-type:text/html;charset=utf-8");
        $handler = opendir($path); //打开当前文件夹由$path指定。
        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") {//文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if (is_dir($path . "/" . $filename)) {// 如果读取的某个对象是文件夹，则递归
                    if (!empty($limit_dir) && !in_array($filename, $limit_dir)) continue;
                    $fileseze = get_dir_size($path . "/" . $filename, $fileseze);
                } else $fileseze += filesize($path . "/" . $filename);//文件大小
            }
        }
        @closedir($path);
        return $fileseze;
    }
}

if (!function_exists('check_http_file_exists')) {
    //判断远程文件是否存在
    function check_http_file_exists($url) {
        $curl = curl_init($url);
        // 不取回数据
        curl_setopt($curl, CURLOPT_NOBODY, true);
        // 发送请求
        $result = curl_exec($curl);
        $found = false;
        // 如果请求没有发送失败
        if ($result !== false) {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) $found = true;
        }
        curl_close($curl);
        return $found;
    }
}

if (!function_exists('auto_incluede_directory_files')) {
    /**
     * 自动引入指定文件夹下方所有的文件
     * @param $path
     */
    function auto_incluede_directory_files($path) {
        $handler = opendir($path); //打开当前文件夹由$path指定。
        while (($filename = readdir($handler)) !== false) {
            if ($filename != '.' && $filename != '..') {//文件夹文件名字为'.'和‘..’，不要对他们进行操作
                // 如果读取的某个对象是文件夹，则递归
                if (is_dir($dir_path = $path . '/' . $filename)) auto_incluede_directory_files($dir_path);
                else if (is_file($file_path = $path . '/' . $filename)) include_once $file_path;
            }
        }
        @closedir($path);
    }
}

/**
 * 切分SQL文件成多个可以单独执行的sql语句
 * @param        $file            string sql文件路径
 * @param        $tablePre        string 表前缀
 * @param string $charset 字符集
 * @param string $defaultTablePre 默认表前缀
 * @param string $defaultCharset 默认字符集
 * @return array
 */
function get_split_sql($file, $tablePre, $charset = 'utf8mb4', $defaultTablePre = '', $defaultCharset = 'utf8mb4') {
    if (file_exists($file)) {
        //读取SQL文件
        $sql = file_get_contents($file);
        $sql = str_replace("\r", "\n", $sql);
        $sql = str_replace("BEGIN;\n", '', $sql);//兼容 navicat 导出的 insert 语句
        $sql = str_replace("COMMIT;\n", '', $sql);//兼容 navicat 导出的 insert 语句
        $sql = str_replace("↵↵", '', $sql);//兼容 navicat 导出的 insert 语句
        $sql = str_replace("\n  ", '', $sql);//兼容 navicat 导出的 insert 语句
        // $sql = str_replace(" ", '', $sql);//兼容 navicat 导出的 insert 语句
        if ($defaultCharset == $charset) {
            $sql = str_replace($defaultCharset, $charset, $sql);
        }
        $sql = trim($sql);
        //替换表前缀
        $sql = str_replace(" `{$defaultTablePre}", " `{$tablePre}", $sql);
        // $sqls = explode("-- ----------------------------", $sql);
        $sqls = explode(";\n", $sql);
        return $sqls;
    }
    return [];
}

//dir文件夹
//获取某目录下所有子文件和子目录,可以过滤
function getDirContent($path, array $filter = [], $onlydir = true) {
    if (!is_dir($path)) {
        return false;
    }
    //readdir方法
    /* $dir = opendir($path);
    $arr = array();
    while($content = readdir($dir)){
        if($content != '.' && $content != '..'){
            $arr[] = $content;
        }
    }
    closedir($dir); */

    //scandir方法
    $arr = array();
    $data = scandir($path);
    foreach ($data as $value) {
        if ($value != '.' && $value != '..' && $value != ".DS_Store" && !in_array($value, $filter)) {
            if ($onlydir) {
                if (is_dir($path . "/" . $value)) {
                    $arr[] = $value;
                } else {
                    continue;
                }
            } else {
                $arr[] = $value;
            }

        }
    }
    return $arr;
}

// 循环创建目录
function mk_dir($dir, $mode = 0755) {
    if (is_dir($dir) || @mkdir($dir, $mode)) return true;
    if (!mk_dir(dirname($dir), $mode)) return false;
    return @mkdir($dir, $mode);
}

function should_backup_update_extract_file(string $filename): bool
{
    $normalized = str_replace('\\', '/', $filename);
    $configRoot = str_replace('\\', '/', base_path('config')) . '/';
    if (preg_match('#/Config/config\.php$#i', $normalized)) {
        return true;
    }

    if (preg_match('#/config\.json$#i', $normalized)) {
        return true;
    }

    return strpos($normalized, $configRoot) === 0 && preg_match('#\.php$#i', $normalized);
}

function update_extract_backup_copy_path(string $filename): string
{
    return $filename . (preg_match('#/config\.json$#i', str_replace('\\', '/', $filename)) ? '_copy.json' : '_copy.php');
}

function backup_update_extract_file(string $filename): void
{
    $copyPath = update_extract_backup_copy_path($filename);
    @copy($filename, $copyPath);
    $temp = cache()->get("update_backup") ?: [];
    if (!in_array($filename, $temp, true)) {
        $temp[] = $filename;
        cache()->put("update_backup", $temp);
    }
}

function callback_pre_extract($p_event,$p_header)
{
    // 获取将要解压缩的文件名
    $filename = $p_header['filename'];
    // 获取文件的修改时间
    $fileMtime = $p_header['mtime'];
    if($p_header['folder']){
        return 1;
    }
    // 检查文件是否存在
    if (file_exists($filename)) {
        // 获取当前文件的修改时间
        $currentMtime = filemtime($filename);

        if (should_backup_update_extract_file($filename)) {
            backup_update_extract_file($filename);
        }

        // 比对修改时间
        if ($fileMtime != $currentMtime) {
            chmod($filename,0777);
            @unlink($filename);
        }
    }
    return 1;
}

