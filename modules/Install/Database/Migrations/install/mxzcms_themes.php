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
        if (Schema::hasTable('themes')) {
            return;
        }

        Schema::create('themes', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->text('name')->nullable();
            $table->text('preview')->nullable();
            $table->string('identification', 255)->nullable();
            $table->integer('status')->nullable()->default(2);
            $table->integer('order')->default(0);
            $table->text('form')->nullable();
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->unique([
    'identification',
], 'themes_identification_uk');
            $table->index([
    'order',
    'status',
], 'themes_order_status_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('themes');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
