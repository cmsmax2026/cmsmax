<?php

namespace Modules\Main\Http\Controllers\Admin;

use App\Support\Telemetry\StatisticReporter;
use Modules\Formtools\Http\Controllers\Admin\FormTool;
use Modules\ModulesController;
use function back;
use function getURIByRoute;
use function url;

class PluginController extends ModulesController {

    public function config() {
        //换成钩子代替
        $all = $this->request->all();
        $identification = trim((string) ($all['identification'] ?? ''));
        if ($identification === '') {
            return redirect(url("admin/plugin"));
        }

        $path = module_path($identification, "Config/config.php", "plugin");
        if (!is_file($path)) {
            return redirect(url("admin/plugin"))->with('errormsg', '插件设置文件不存在，请重新安装该插件');
        }

        $configArr = include $path;
        if (!is_array($configArr)) {
            return redirect(url("admin/plugin"))->with('errormsg', '插件设置文件格式错误，请重新安装该插件');
        }

        $config = $configArr['config'] ?? [];
        $config = is_array($config) ? $config : [];

        if ($this->request->isMethod('POST')) {
            // 白名单：只接受清单里已声明的配置项，避免把 _method、分页参数等杂项写进文件
            $updated = 0;
            foreach (array_keys($config) as $key) {
                if (!array_key_exists($key, $all)) {
                    continue;
                }
                $config[$key]['value'] = $all[$key];
                $updated++;
            }

            $configArr['config'] = $config;
            $result = $this->writePluginConfig($path, $configArr);

            if (($result['status'] ?? 0) !== 200) {
                return back()->with('errormsg', $result['msg'] ?? '保存失败');
            }

            $message = '保存成功（更新了 ' . $updated . ' 项）';
            if (!empty($result['backup'])) {
                $message .= '，修改前的配置已自动备份';
            }

            return back()->with('successmsg', $message);
        }

        $pageData = getURIByRoute($this->request);
        $create = FormTool::create()
            ->csrf_field()
            ->field('identification', ['value' => $identification, 'formtype' => 'hidden'])
            ->formTitle($configArr['name']." - 插件配置")
            ->pageTitle("插件管理")
            ->formaction(url($pageData['uri']));

        foreach ($config as $key => $c) {
            $create->field($key, [
                'name' => $c['name'] ?? $key,
                'value' => $c['value'] ?? '',
                'formtype' => $c['formtype'] ?? 'text',
                'datas' => $c['datas'] ?? [],
                'notes' => $c['notes'] ?? '',
            ]);
        }

        $pageData = $create->getData();
        $pageData['configPath'] = $path;
        $pageData['configEmpty'] = empty($config);
        StatisticReporter::reportSuccess('Usage', $identification, \Modules\Main\Models\Modules::Plugin, [
            'entry' => 'admin_plugin_config',
        ]);

        return view('admin/func/plugin-config', compact('pageData'));
    }

    /**
     * 安全写回插件配置文件。
     *
     * 配置直接以 var_export 形式写在插件自己的 Config/config.php 里，写坏了整个插件就废了，
     * 所以按「备份 → 写临时文件 → 语法校验 → 替换 → 失效 opcache」的顺序来。
     */
    private function writePluginConfig(string $path, array $configArr): array
    {
        if (!is_writable($path) && !is_writable(dirname($path))) {
            return ['status' => 0, 'msg' => '插件设置文件不可写，请联系管理员检查服务器上插件目录的写入权限'];
        }

        // 1. 备份当前配置
        $backup = null;
        if (@copy($path, $path . '.bak')) {
            $backup = $path . '.bak';
        }

        // 2. 先落到临时文件
        $content = "<?php\r\n\r\n return " . var_export($configArr, true) . ";";
        $tmpPath = $path . '.' . getmypid() . '.tmp';
        if (@file_put_contents($tmpPath, $content) === false) {
            @unlink($tmpPath);
            return ['status' => 0, 'msg' => '临时文件写入失败，配置未改动'];
        }

        // 3. 语法校验：include 一个坏文件会抛 ParseError，比依赖 php -l 更可控
        $valid = false;
        try {
            $parsed = @include $tmpPath;
            $valid = is_array($parsed);
        } catch (\ParseError $exception) {
            $valid = false;
        } catch (\Throwable $exception) {
            $valid = false;
        }

        if (!$valid) {
            @unlink($tmpPath);
            return ['status' => 0, 'msg' => '生成的设置文件校验未通过，已取消保存，原有设置保持不变'];
        }

        // 4. 替换（Windows 上 rename 覆盖失败时退回 copy）
        $replaced = @rename($tmpPath, $path);
        if (!$replaced) {
            $replaced = @copy($tmpPath, $path);
            @unlink($tmpPath);
        }
        if (!$replaced) {
            @unlink($tmpPath);
            return ['status' => 0, 'msg' => '配置文件替换失败，配置未改动'];
        }

        @chmod($path, 0644);

        // 5. 开了 opcache 的环境必须失效，否则页面读到的还是旧配置
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($path, true);
        }

        return ['status' => 200, 'msg' => 'ok', 'backup' => $backup];
    }

}
