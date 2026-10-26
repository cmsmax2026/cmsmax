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
        if (Schema::hasTable('system_message')) {
            return;
        }

        Schema::create('system_message', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->text('module')->nullable();
            $table->text('title')->nullable();
            $table->text('content')->nullable();
            $table->integer('uid')->nullable()->default(0);
            $table->integer('receive_uid')->nullable()->default(0);
            $table->integer('status')->nullable()->default(0);
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->text('json_str')->nullable();
            $table->index([
    'status',
], 'message_status_idx');
            $table->index([
    'uid',
], 'message_uid_idx');
            $table->index([
    'receive_uid',
], 'message_receive_uid_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_message');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
