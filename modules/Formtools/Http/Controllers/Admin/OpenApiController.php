<?php

namespace Modules\Formtools\Http\Controllers\Admin;

use Modules\Formtools\Models\FormModel;
use Modules\Formtools\Models\OpenApiApp;
use Modules\Formtools\Models\OpenApiLog;
use Modules\Formtools\Support\FormOpenApiService;
use Modules\ModulesController;

class OpenApiController extends ModulesController
{
    public function modelApi()
    {
        $model = $this->resolveModelFromRequest();
        if (!$model) {
            return redirect('/admin/formtools/index')->with('pageDataMsg', '模型不存在');
        }

        if ($this->request->isMethod('post')) {
            $enabled = (int) $this->request->input('enabled', 0);
            if ($enabled === 1 && trim((string) $model->access_identification) === '') {
                return back()->withInput()->with('pageDataMsg', '当前模型还没配置访问标识，先把访问标识填上，才能启用开放 API。');
            }

            $this->service()->saveModelConfig($model, [
                'enabled' => $enabled,
                'allow_seo' => (int) $this->request->input('allow_seo', 1),
                'default_status' => (int) $this->request->input('default_status', 1),
                'ip_whitelist' => (string) $this->request->input('ip_whitelist', ''),
                'remark' => (string) $this->request->input('remark', ''),
            ]);

            return redirect('/admin/formtools/modelApi?id=' . $model->id)->with([
                'pageDataMsg' => '开放 API 配置已保存',
                'pageDataStatus' => 200,
            ]);
        }

        $pageData = getURIByRoute($this->request);
        $pageData['title'] = '模型开放 API';
        $pageData['subtitle'] = $model->name;
        $pageData['currentModel'] = $model;

        return view('formtools::admin.index.modelApi', [
            'pageData' => $pageData,
            'model' => $model,
            'globalSettings' => $this->service()->globalSettings(),
            'modelConfig' => $this->service()->modelConfig($model),
            'metaDocument' => $this->service()->buildMetaDocument($model),
            'apps' => OpenApiApp::query()
                ->where('model_id', $model->id)
                ->orderByDesc('id')
                ->get(),
            'logs' => OpenApiLog::query()
                ->where('model_id', $model->id)
                ->orderByDesc('id')
                ->paginate(15),
            'newSecretInfo' => session('openapi_secret_info'),
        ]);
    }

    public function createApp()
    {
        $model = $this->resolveModelFromRequest();
        if (!$model) {
            return redirect('/admin/formtools/index')->with('pageDataMsg', '模型不存在');
        }

        if (trim((string) $model->access_identification) === '') {
            return redirect('/admin/formtools/modelApi?id=' . $model->id)->with('pageDataMsg', '请先配置访问标识，再创建开放应用');
        }

        [$app, $secret] = $this->service()->createApp($model, [
            'app_name' => $this->request->input('app_name', ''),
            'status' => $this->request->input('status', 1),
            'rate_limit' => $this->request->input('rate_limit', 60),
            'ip_whitelist' => $this->request->input('ip_whitelist', ''),
            'remark' => $this->request->input('remark', ''),
        ]);

        return redirect('/admin/formtools/modelApi?id=' . $model->id)->with([
            'pageDataMsg' => '开放应用已创建，请立即复制密钥',
            'pageDataStatus' => 200,
            'openapi_secret_info' => [
                'app_name' => $app->app_name,
                'app_id' => $app->app_id,
                'app_secret' => $secret,
            ],
        ]);
    }

    public function appStatus()
    {
        $model = $this->resolveModelFromRequest();
        if (!$model) {
            return redirect('/admin/formtools/index')->with('pageDataMsg', '模型不存在');
        }

        $app = OpenApiApp::query()
            ->where('id', (int) $this->request->input('app_row_id', 0))
            ->where('model_id', $model->id)
            ->first();

        if (!$app) {
            return redirect('/admin/formtools/modelApi?id=' . $model->id)->with('pageDataMsg', '开放应用不存在');
        }

        $status = (int) $this->request->input('status', 1) === 1 ? 1 : 0;
        $app->forceFill([
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ])->save();

        return redirect('/admin/formtools/modelApi?id=' . $model->id)->with([
            'pageDataMsg' => $status === 1 ? '开放应用已启用' : '开放应用已停用',
            'pageDataStatus' => 200,
        ]);
    }

    public function resetSecret()
    {
        $model = $this->resolveModelFromRequest();
        if (!$model) {
            return redirect('/admin/formtools/index')->with('pageDataMsg', '模型不存在');
        }

        $app = OpenApiApp::query()
            ->where('id', (int) $this->request->input('app_row_id', 0))
            ->where('model_id', $model->id)
            ->first();

        if (!$app) {
            return redirect('/admin/formtools/modelApi?id=' . $model->id)->with('pageDataMsg', '开放应用不存在');
        }

        $secret = $this->service()->rotateSecret($app);

        return redirect('/admin/formtools/modelApi?id=' . $model->id)->with([
            'pageDataMsg' => '密钥已重置，请立即同步到调用方',
            'pageDataStatus' => 200,
            'openapi_secret_info' => [
                'app_name' => $app->app_name,
                'app_id' => $app->app_id,
                'app_secret' => $secret,
            ],
        ]);
    }

    private function resolveModelFromRequest(): ?FormModel
    {
        $id = (string) ($this->request->input('id', $this->request->query('id', '')));
        if ($id === '') {
            return null;
        }

        return FormModel::query()->withoutReserved()->find($id);
    }

    private function service(): FormOpenApiService
    {
        return app(FormOpenApiService::class);
    }
}
