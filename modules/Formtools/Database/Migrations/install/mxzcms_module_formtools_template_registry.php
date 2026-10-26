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
        if (Schema::hasTable('module_formtools_template_registry')) {
            return;
        }

        Schema::create('module_formtools_template_registry', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->string('scene', 255);
            $table->string('template_key', 255);
            $table->string('template_name', 255);
            $table->text('description')->nullable();
            $table->text('required_fields')->nullable();
            $table->text('recommended_fields')->nullable();
            $table->text('supported_model_type')->nullable();
            $table->integer('status')->default(1);
            $table->integer('sort')->default(0);
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->unique([
    'scene',
    'template_key',
], 'formtools_template_registry_scene_template_key_uk');
            $table->index([
    'scene',
    'status',
], 'formtools_template_registry_scene_status_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_formtools_template_registry');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
