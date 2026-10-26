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
        if (Schema::hasTable('module_formtools_agreement')) {
            return;
        }

        Schema::create('module_formtools_agreement', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->integer('cate_id')->nullable();
            $table->text('name')->nullable();
            $table->text('content')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->text('seo_description')->nullable();
            $table->integer('status')->nullable();
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->text('remark')->nullable();
            $table->text('seo_title')->nullable();
            $table->integer('download_count')->nullable()->default(0);
            $table->integer('comment_count')->nullable()->default(0);
            $table->integer('good_count')->nullable()->default(0);
            $table->integer('access_count')->nullable()->default(0);
            $table->integer('uid')->nullable();
            $table->float('price')->nullable()->default(0);
            // no additional indexes detected
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_formtools_agreement');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
