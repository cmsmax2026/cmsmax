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
        if (Schema::hasTable('module_member_bill_record')) {
            return;
        }

        Schema::create('module_member_bill_record', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->integer('uid')->nullable()->default(0);
            $table->string('module', 255)->nullable();
            $table->string('bill_order_num', 255)->nullable();
            $table->string('order_num', 255)->nullable();
            $table->double('amount')->nullable()->default(0.00);
            $table->text('remark')->nullable();
            $table->text('json_str')->nullable();
            $table->string('created_at', 255)->nullable();
            $table->text('updated_at')->nullable();
            $table->index([
    'bill_order_num',
], 'member_bill_record_bill_order_num_idx');
            $table->index([
    'created_at',
], 'member_bill_record_created_at_idx');
            $table->index([
    'order_num',
], 'member_bill_record_order_num_idx');
            $table->index([
    'module',
], 'member_bill_record_module_idx');
            $table->index([
    'uid',
], 'member_bill_record_uid_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_member_bill_record');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
