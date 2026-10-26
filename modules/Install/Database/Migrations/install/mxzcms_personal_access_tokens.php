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
        if (Schema::hasTable('personal_access_tokens')) {
            return;
        }

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->text('tokenable_type');
            $table->integer('tokenable_id');
            $table->text('name');
            $table->text('token');
            $table->text('abilities')->nullable();
            $table->text('last_used_at')->nullable();
            $table->text('expires_at')->nullable();
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            // no additional indexes detected
        });
    }

    public function down()
    {
        Schema::dropIfExists('personal_access_tokens');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
