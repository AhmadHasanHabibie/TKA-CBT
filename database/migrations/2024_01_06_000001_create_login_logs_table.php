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
        if (!Schema::hasTable('login_logs')) {
            Schema::create('login_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('session_id', 100)->nullable()->index();
                $table->string('event_type', 30)->default('visit');
                $table->string('attempted_email', 191)->nullable()->index();
                $table->string('school_name', 255)->nullable()->index();
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
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'last_login_at')) {
                    $table->timestamp('last_login_at')->nullable()->after('role');
                }
                if (!Schema::hasColumn('users', 'last_login_ip')) {
                    $table->string('last_login_ip', 64)->nullable()->after('last_login_at');
                }
                if (!Schema::hasColumn('users', 'last_login_location')) {
                    $table->string('last_login_location')->nullable()->after('last_login_ip');
                }
                if (!Schema::hasColumn('users', 'last_login_browser')) {
                    $table->string('last_login_browser', 120)->nullable()->after('last_login_location');
                }
                if (!Schema::hasColumn('users', 'last_login_os')) {
                    $table->string('last_login_os', 120)->nullable()->after('last_login_browser');
                }
                if (!Schema::hasColumn('users', 'last_login_latitude')) {
                    $table->decimal('last_login_latitude', 10, 7)->nullable()->after('last_login_os');
                }
                if (!Schema::hasColumn('users', 'last_login_longitude')) {
                    $table->decimal('last_login_longitude', 10, 7)->nullable()->after('last_login_latitude');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'last_login_at')) {
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
    }
};
