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
        if (Schema::hasTable('module_formtools_openapi_apps')) {
            return;
        }

        Schema::create('module_formtools_openapi_apps', function (Blueprint $table) {
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
            $table->string('app_name', 255);
            $table->string('app_id', 255);
            $table->text('app_secret')->nullable();
            $table->string('secret_hint', 255)->nullable();
            $table->integer('status')->default(1);
            $table->integer('rate_limit')->default(60);
            $table->text('ip_whitelist')->nullable();
            $table->text('remark')->nullable();
            $table->text('last_used_at')->nullable();
            $table->string('last_ip', 255)->nullable();
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->unique([
    'app_id',
], 'formtools_openapi_apps_app_id_uk');
            $table->index([
    'model_id',
    'status',
], 'formtools_openapi_apps_model_id_status_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_formtools_openapi_apps');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
