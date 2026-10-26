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
        if (Schema::hasTable('module_member_wallet')) {
            return;
        }

        Schema::create('module_member_wallet', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('wallet_id');
            $table->integer('uid')->nullable();
            $table->double('withdrawable')->nullable()->default(0.00);
            $table->double('balance')->nullable()->default(0.00);
            $table->double('integral')->nullable()->default(0.00);
            $table->text('vip_time')->nullable();
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            // no additional indexes detected
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_member_wallet');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
