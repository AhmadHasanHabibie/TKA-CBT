<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 100)->nullable()->index();
            $table->string('event_type', 30)->default('visit'); // 'visit' (klik link tanpa login) or 'login'
            $table->string('visited_url', 500)->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->string('location')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('browser', 120)->nullable();
            $table->string('os', 120)->nullable();
            $table->string('device_type', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('logged_in_at')->useCurrent();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('role');
            $table->string('last_login_ip', 64)->nullable()->after('last_login_at');
            $table->string('last_login_location')->nullable()->after('last_login_ip');
            $table->string('last_login_browser', 120)->nullable()->after('last_login_location');
            $table->string('last_login_os', 120)->nullable()->after('last_login_browser');
            $table->decimal('last_login_latitude', 10, 7)->nullable()->after('last_login_os');
            $table->decimal('last_login_longitude', 10, 7)->nullable()->after('last_login_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_login_at',
                'last_login_ip',
                'last_login_location',
                'last_login_browser',
                'last_login_os',
                'last_login_latitude',
                'last_login_longitude',
            ]);
        });
    }
};
