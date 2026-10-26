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
        if (Schema::hasTable('system_ip_block_logs')) {
            return;
        }

        Schema::create('system_ip_block_logs', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->string('ip', 255)->nullable();
            $table->string('scope', 255)->nullable();
            $table->string('source', 255)->nullable();
            $table->integer('rule_id')->nullable()->default(0);
            $table->string('rule_value', 255)->nullable();
            $table->text('request_path')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('reason')->nullable();
            $table->string('created_at', 255)->nullable();
            $table->index([
    'scope',
    'source',
], 'ip_block_logs_scope_source_idx');
            $table->index([
    'created_at',
], 'ip_block_logs_created_at_idx');
            $table->index([
    'ip',
], 'ip_block_logs_ip_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_ip_block_logs');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
