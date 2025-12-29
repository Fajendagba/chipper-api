<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::create('favorites_new', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('post_id')->nullable();
                $table->foreignId('user_id')->constrained();
                $table->unsignedBigInteger('favoritable_id')->nullable();
                $table->string('favoritable_type')->nullable();
                $table->timestamps();
                $table->index(['favoritable_type', 'favoritable_id']);
                $table->unique(['user_id', 'favoritable_id', 'favoritable_type'], 'favorites_user_favoritable_unique');
            });

            DB::statement('INSERT INTO favorites_new (id, post_id, user_id, favoritable_id, favoritable_type, created_at, updated_at) SELECT id, post_id, user_id, post_id, \'App\Models\Post\', created_at, updated_at FROM favorites');

            Schema::drop('favorites');
            Schema::rename('favorites_new', 'favorites');
        } else {
            Schema::table('favorites', function (Blueprint $table) {
                $table->unsignedBigInteger('post_id')->nullable()->change();
                $table->unsignedBigInteger('favoritable_id')->nullable()->after('post_id');
                $table->string('favoritable_type')->nullable()->after('favoritable_id');
                $table->index(['favoritable_type', 'favoritable_id']);
                $table->unique(['user_id', 'favoritable_id', 'favoritable_type'], 'favorites_user_favoritable_unique');
            });

            DB::statement("UPDATE favorites SET favoritable_id = post_id, favoritable_type = 'App\\\\Models\\\\Post' WHERE post_id IS NOT NULL");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::create('favorites_new', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('post_id');
                $table->foreignId('user_id')->constrained();
                $table->timestamps();
            });

            DB::statement('INSERT INTO favorites_new (id, post_id, user_id, created_at, updated_at) SELECT id, post_id, user_id, created_at, updated_at FROM favorites WHERE post_id IS NOT NULL');

            Schema::drop('favorites');
            Schema::rename('favorites_new', 'favorites');
        } else {
            DB::statement('DELETE FROM favorites WHERE post_id IS NULL');

            Schema::table('favorites', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            Schema::table('favorites', function (Blueprint $table) {
                $table->dropUnique('favorites_user_favoritable_unique');
                $table->dropIndex('favorites_favoritable_type_favoritable_id_index');
                $table->dropColumn(['favoritable_id', 'favoritable_type']);
            });

            Schema::table('favorites', function (Blueprint $table) {
                $table->unsignedBigInteger('post_id')->nullable(false)->change();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
};
