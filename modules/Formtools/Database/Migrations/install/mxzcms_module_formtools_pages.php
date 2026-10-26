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
        if (Schema::hasTable('module_formtools_pages')) {
            return;
        }

        Schema::create('module_formtools_pages', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->text('name');
            $table->string('identification', 255);
            $table->string('slug', 255);
            $table->text('type');
            $table->integer('category_id')->nullable();
            $table->integer('model_id')->nullable();
            $table->text('template');
            $table->text('builder_type');
            $table->integer('status')->default(1);
            $table->integer('is_nav')->default(0);
            $table->integer('is_home')->default(0);
            $table->integer('sort')->default(0);
            $table->text('remark');
            $table->text('seo_title')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('layout_schema')->nullable();
            $table->text('page_html')->nullable();
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->unique([
    'identification',
], 'formtools_pages_identification_uk');
            $table->unique([
    'slug',
], 'formtools_pages_slug_uk');
            $table->index([
    'model_id',
], 'formtools_pages_model_id_idx');
            $table->index([
    'status',
], 'formtools_pages_status_idx');
            $table->index([
    'sort',
], 'formtools_pages_sort_idx');
            $table->index([
    'category_id',
], 'formtools_pages_category_id_idx');
            $table->index([
    'is_home',
], 'formtools_pages_is_home_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_formtools_pages');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
