<?php

namespace Modules\System\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Formtools\Models\FormModel;
use Modules\Formtools\Models\FormPage;

class GetSiteMapUrl
{
    protected array $columnCache = [];

    protected function baseUrls(): array
    {
        return [
            '/',
            'index',
            'about',
            'contacts',
            'login',
            'register',
        ];
    }

    protected function modelListUrls(): array
    {
        $urls = [];
        $models = FormModel::query()
            ->withoutReserved()
            ->get(['identification', 'access_identification', 'home_config']);

        foreach ($models as $model) {
            $access = trim((string) $model->access_identification);
            if ($access === '') {
                continue;
            }
            $homeConfig = [
                'home_config' => json_decode((string) ($model->home_config ?? '[]'), true) ?: [],
            ];
            $urls[] = model_list_url($access, [], $homeConfig);
        }

        return $urls;
    }

    protected function modelDetailUrls(): array
    {
        $urls = [];
        $models = FormModel::query()
            ->withoutReserved()
            ->get(['identification', 'access_identification', 'other_config', 'home_config']);

        foreach ($models as $model) {
            $access = trim((string) $model->access_identification);
            $identification = trim((string) $model->identification);
            if ($access === '' || $identification === '') {
                continue;
            }

            $otherConfig = json_decode((string) $model->other_config, true) ?: [];
            $homeConfig = [
                'home_config' => json_decode((string) ($model->home_config ?? '[]'), true) ?: [],
            ];
            if (($otherConfig['data_source'] ?? 'local') !== 'local') {
                continue;
            }

            $tableName = 'module_formtools_' . $identification;
            if (!Schema::hasTable($tableName) || !$this->hasColumn($tableName, 'id')) {
                continue;
            }

            $query = DB::table($tableName)->select('id')->orderByDesc('id');
            if ($this->hasColumn($tableName, 'status')) {
                $query->where('status', 1);
            }

            foreach ($query->pluck('id') as $id) {
                $urls[] = model_detail_url($access, $id, [], $homeConfig);
            }
        }

        return $urls;
    }

    protected function pageUrls(): array
    {
        $urls = [];
        $pages = FormPage::query()
            ->enabled()
            ->get(['slug']);

        foreach ($pages as $page) {
            $slug = trim((string) $page->slug, '/');
            if ($slug === '') {
                continue;
            }
            $urls[] = 'p/' . $slug;
        }

        return $urls;
    }

    protected function hasColumn(string $tableName, string $column): bool
    {
        $cacheKey = $tableName . '.' . $column;
        if (!array_key_exists($cacheKey, $this->columnCache)) {
            $this->columnCache[$cacheKey] = Schema::hasColumn($tableName, $column);
        }

        return $this->columnCache[$cacheKey];
    }

    protected function normalizeUrls(array $urls): array
    {
        $normalized = [];
        foreach ($urls as $url) {
            $url = trim((string) $url);
            if ($url === '') {
                continue;
            }
            $normalized[] = preg_match('#^https?://#i', $url) ? $url : url(ltrim($url, '/'));
        }

        return array_values(array_unique($normalized));
    }

    public function handle( \App\Events\GetSiteMapUrl $event) {
        $urls = array_merge(
            $this->baseUrls(),
            $this->modelListUrls(),
            $this->modelDetailUrls(),
            $this->pageUrls(),
        );

        return $this->normalizeUrls($urls);

    }

}
