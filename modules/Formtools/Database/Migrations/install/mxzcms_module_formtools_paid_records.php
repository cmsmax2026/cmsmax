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
        if (Schema::hasTable('module_formtools_paid_records')) {
            return;
        }

        Schema::create('module_formtools_paid_records', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->integer('user_id')->default(0);
            $table->string('user_type', 255)->default('');
            $table->string('user_label', 255)->default('');
            $table->string('model_identification', 255);
            $table->integer('content_id');
            $table->string('content_title', 255)->default('');
            $table->string('order_no', 255);
            $table->decimal('amount', 10, 0)->default(0);
            $table->string('pay_method', 255)->default('');
            $table->string('pay_type', 255)->default('');
            $table->integer('status')->default(0);
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('expire_at')->nullable();
            $table->string('out_trade_no', 255)->default('');
            $table->text('extra')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->index([
    'status',
], 'formtools_paid_records_status_idx');
            $table->index([
    'user_id',
    'user_type',
], 'formtools_paid_records_user_id_user_type_idx');
            $table->index([
    'model_identification',
    'content_id',
], 'formtools_paid_records_model_identification_content_id_idx');
            $table->unique([
    'order_no',
], 'formtools_paid_records_order_no_uk');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_formtools_paid_records');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
