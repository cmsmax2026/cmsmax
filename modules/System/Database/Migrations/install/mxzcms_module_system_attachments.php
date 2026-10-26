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
        if (Schema::hasTable('module_system_attachments')) {
            return;
        }

        Schema::create('module_system_attachments', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->string('path', 255);
            $table->string('path_md5', 255);
            $table->text('drive');
            $table->text('create_at')->nullable();
            $table->text('update_at')->nullable();
            $table->text('deleted_at')->nullable();
            $table->text('deleted_by');
            $table->text('is_star');
            $table->text('share_token');
            $table->text('share_expire_at')->nullable();
            $table->text('is_private');
            $table->text('access_key');
            $table->integer('share_visit_total')->default(0);
            $table->text('share_last_visit_at')->nullable();
            $table->index([
    'path_md5',
], 'system_attachments_path_md5_idx');
            $table->index([
    'path',
], 'system_attachments_path_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_system_attachments');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
