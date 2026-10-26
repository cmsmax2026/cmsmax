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
        if (Schema::hasTable('home_menu')) {
            return;
        }

        Schema::create('home_menu', function (Blueprint $table) {
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
            $table->string('position', 255)->nullable();
            $table->string('lang', 255);
            $table->integer('pid')->nullable()->default(0);
            $table->text('name')->nullable();
            $table->text('url')->nullable();
            $table->text('target');
            $table->text('icon')->nullable();
            $table->text('icon_character')->nullable();
            $table->text('menu_type');
            $table->text('source_module');
            $table->text('source_value');
            $table->text('cover')->nullable();
            $table->integer('status')->nullable()->default(1);
            $table->integer('sort')->nullable()->default(0);
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->index([
    'position',
], 'home_menu_position_idx');
            $table->index([
    'position',
    'lang',
], 'home_menu_position_lang_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('home_menu');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
