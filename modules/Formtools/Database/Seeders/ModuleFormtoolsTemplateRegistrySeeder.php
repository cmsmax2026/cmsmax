<?php

namespace Modules\Formtools\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Formtools\Support\FormTemplateCatalog;

class ModuleFormtoolsTemplateRegistrySeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('module_formtools_template_registry')) {
            return;
        }

        foreach (FormTemplateCatalog::templates() as $template) {
            DB::table('module_formtools_template_registry')->updateOrInsert([
                'scene' => $template['scene'],
                'template_key' => $template['template_key'],
            ], [
                'template_name' => $template['template_name'],
                'description' => $template['description'] ?? '',
                'required_fields' => json_encode($template['required_fields'] ?? [], JSON_UNESCAPED_UNICODE),
                'recommended_fields' => json_encode($template['recommended_fields'] ?? [], JSON_UNESCAPED_UNICODE),
                'supported_model_type' => json_encode($template['supported_model_type'] ?? [], JSON_UNESCAPED_UNICODE),
                'status' => 1,
                'sort' => (int) ($template['sort'] ?? 0),
                'updated_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
