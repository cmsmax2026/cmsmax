<?php

use App\Support\Database\DatabaseCharsetResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('module_formtools_model_template_maps')) {
            return;
        }

        Schema::create('module_formtools_model_template_maps', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->string('model_id', 255);
            $table->string('scene', 255);
            $table->string('mode', 255)->default('preset');
            $table->string('template_key', 255)->nullable();
            $table->text('custom_template')->nullable();
            $table->text('field_map')->nullable();
            $table->text('match_summary')->nullable();
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->unique([
    'model_id',
    'scene',
], 'formtools_model_template_maps_model_id_scene_uk');
            $table->index([
    'scene',
    'mode',
], 'formtools_model_template_maps_scene_mode_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_formtools_model_template_maps');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
