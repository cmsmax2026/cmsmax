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
        if (Schema::hasTable('module_formtools_page_categories')) {
            return;
        }

        Schema::create('module_formtools_page_categories', function (Blueprint $table) {
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
            $table->integer('sort')->default(0);
            $table->integer('status')->default(1);
            $table->text('remark');
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->unique([
    'identification',
], 'formtools_page_categories_identification_uk');
            $table->index([
    'sort',
], 'formtools_page_categories_sort_idx');
            $table->index([
    'status',
], 'formtools_page_categories_status_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_formtools_page_categories');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
