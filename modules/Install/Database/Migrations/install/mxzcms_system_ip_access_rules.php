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
        if (Schema::hasTable('system_ip_access_rules')) {
            return;
        }

        Schema::create('system_ip_access_rules', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->string('scope', 255);
            $table->string('rule_type', 255);
            $table->text('match_type');
            $table->string('rule_value', 255);
            $table->text('source_type');
            $table->integer('status')->default(1);
            $table->text('reason')->nullable();
            $table->string('expires_at', 255)->nullable();
            $table->integer('hit_count')->default(0);
            $table->text('last_hit_at')->nullable();
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->index([
    'scope',
    'status',
    'rule_type',
], 'ip_access_rules_scope_status_rule_type_idx');
            $table->index([
    'rule_value',
], 'ip_access_rules_rule_value_idx');
            $table->index([
    'expires_at',
], 'ip_access_rules_expires_at_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_ip_access_rules');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
