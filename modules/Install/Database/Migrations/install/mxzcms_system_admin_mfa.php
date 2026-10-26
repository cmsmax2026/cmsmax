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
        if (Schema::hasTable('system_admin_mfa')) {
            return;
        }

        Schema::create('system_admin_mfa', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->integer('uid')->default(0);
            $table->string('provider', 255)->default('google_authenticator');
            $table->text('secret')->nullable();
            $table->integer('status')->default(1);
            $table->dateTime('bound_at')->nullable();
            $table->dateTime('last_verified_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unique([
    'uid',
    'provider',
], 'admin_mfa_uid_provider_uk');
            $table->index([
    'status',
    'provider',
], 'admin_mfa_status_provider_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_admin_mfa');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
