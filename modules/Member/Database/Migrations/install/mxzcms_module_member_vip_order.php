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
        if (Schema::hasTable('module_member_vip_order')) {
            return;
        }

        Schema::create('module_member_vip_order', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('order_id');
            $table->text('payment_id')->nullable();
            $table->string('order_num', 255);
            $table->integer('uid');
            $table->double('price')->default(0.00);
            $table->integer('vip_id')->default(0);
            $table->integer('type')->nullable()->default(0);
            $table->integer('number')->nullable()->default(0);
            $table->text('pay_method')->nullable();
            $table->text('pay_type')->nullable();
            $table->integer('pay_status')->nullable()->default(0);
            $table->text('remark')->nullable();
            $table->text('callback_msg')->nullable();
            $table->text('pay_at')->nullable();
            $table->string('created_at', 255)->nullable();
            $table->text('expire_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->index([
    'created_at',
], 'member_vip_order_created_at_idx');
            $table->index([
    'uid',
], 'member_vip_order_uid_idx');
            $table->index([
    'order_num',
], 'member_vip_order_order_num_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_member_vip_order');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
