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
        if (Schema::hasTable('module_system_scheduled_tasks')) {
            return;
        }

        Schema::create('module_system_scheduled_tasks', function (Blueprint $table) {
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
            $table->integer('task_type')->nullable()->default(1);
            $table->text('name')->nullable();
            $table->integer('type')->nullable()->default(1);
            $table->integer('day')->nullable()->default(1);
            $table->integer('hour')->nullable()->default(0);
            $table->integer('minute')->nullable()->default(0);
            $table->integer('status')->nullable()->default(1);
            $table->text('last_execution_time')->nullable();
            $table->text('content')->nullable();
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->text('module_class')->nullable();
            $table->text('module_class_method')->nullable();
            $table->text('remark')->nullable();
            // no additional indexes detected
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_system_scheduled_tasks');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
