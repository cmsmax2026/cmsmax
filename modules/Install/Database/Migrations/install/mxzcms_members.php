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
        if (Schema::hasTable('members')) {
            return;
        }

        Schema::create('members', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('uid');
            $table->text('avatar')->nullable();
            $table->text('c_code')->nullable();
            $table->string('phone', 255)->nullable();
            $table->text('email')->nullable();
            $table->string('username', 255)->nullable();
            $table->text('nickname')->nullable();
            $table->string('password', 255)->nullable();
            $table->integer('status')->nullable()->default(0);
            $table->integer('pid')->nullable()->default(1);
            $table->text('pid_path')->nullable();
            $table->text('male')->nullable();
            $table->text('birthday')->nullable();
            $table->integer('phone_active')->nullable();
            $table->integer('email_active')->nullable();
            $table->text('signature')->nullable();
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->index([
    'phone',
], 'members_phone_idx');
            $table->index([
    'username',
], 'members_username_idx');
            $table->index([
    'password',
], 'members_password_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('members');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
