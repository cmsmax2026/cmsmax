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
        if (Schema::hasTable('module_formtools_models')) {
            return;
        }

        Schema::create('module_formtools_models', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->text('name')->nullable();
            $table->string('identification', 255)->nullable();
            $table->string('access_identification', 255)->nullable();
            $table->text('menuname')->nullable();
            $table->text('module')->nullable();
            $table->text('fields')->nullable();
            $table->text('icon')->nullable();
            $table->text('type')->nullable();
            $table->integer('supermodel')->nullable();
            $table->text('remark')->nullable();
            $table->text('admin_config')->nullable();
            $table->text('home_config')->nullable();
            $table->text('home_seo_config')->nullable();
            $table->text('home_seo_detail_config')->nullable();
            $table->text('other_config')->nullable();
            $table->text('show_home_page')->nullable();
            $table->integer('home_page_num')->nullable();
            $table->integer('home_page_sort')->nullable()->default(0);
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->unique([
    'identification',
], 'formtools_models_identification_uk');
            $table->index([
    'access_identification',
], 'formtools_models_access_identification_idx');
            $table->index([
    'supermodel',
], 'formtools_models_supermodel_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_formtools_models');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
