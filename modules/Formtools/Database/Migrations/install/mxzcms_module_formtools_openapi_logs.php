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
        if (Schema::hasTable('module_formtools_openapi_logs')) {
            return;
        }

        Schema::create('module_formtools_openapi_logs', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->string('model_id', 255)->nullable();
            $table->string('app_id', 255)->nullable();
            $table->string('access_identification', 255)->nullable();
            $table->string('request_id', 255)->nullable();
            $table->string('channel', 255)->default('openapi');
            $table->string('http_method', 255)->default('POST');
            $table->integer('status_code')->default(0);
            $table->string('status_text', 255)->default('failed');
            $table->text('message')->nullable();
            $table->string('client_ip', 255)->nullable();
            $table->string('nonce', 255)->nullable();
            $table->string('idempotency_key', 255)->nullable();
            $table->string('payload_hash', 255)->nullable();
            $table->text('request_payload')->nullable();
            $table->text('response_payload')->nullable();
            $table->text('created_at')->nullable();
            $table->index([
    'model_id',
], 'formtools_openapi_logs_model_id_idx');
            $table->index([
    'app_id',
], 'formtools_openapi_logs_app_id_idx');
            $table->index([
    'request_id',
], 'formtools_openapi_logs_request_id_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_formtools_openapi_logs');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
