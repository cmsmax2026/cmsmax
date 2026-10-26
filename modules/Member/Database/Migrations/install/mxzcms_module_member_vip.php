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
        if (Schema::hasTable('module_member_vip')) {
            return;
        }

        Schema::create('module_member_vip', function (Blueprint $table) {
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
            $table->integer('type')->nullable()->default(0);
            $table->integer('number')->nullable()->default(0);
            $table->double('price')->nullable()->default(0.00);
            $table->double('discount_price')->nullable()->default(0.00);
            $table->text('describe')->nullable();
            $table->text('tig')->nullable();
            $table->integer('status')->nullable()->default(1);
            $table->integer('sort')->nullable()->default(0);
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->integer('is_only_buy_one')->nullable()->default(2);
            $table->index([
    'status',
], 'member_vip_status_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_member_vip');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
