<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('login_logs')) {
            return;
        }

        Schema::table('login_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('login_logs', 'district')) {
                $table->string('district', 120)->nullable()->after('location');
            }
            if (!Schema::hasColumn('login_logs', 'isp')) {
                $table->string('isp', 191)->nullable()->after('country');
            }
            if (!Schema::hasColumn('login_logs', 'org')) {
                $table->string('org', 191)->nullable()->after('isp');
            }
            if (!Schema::hasColumn('login_logs', 'hostname')) {
                $table->string('hostname', 255)->nullable()->after('org');
            }
            if (!Schema::hasColumn('login_logs', 'is_mobile_network')) {
                $table->boolean('is_mobile_network')->default(false)->after('hostname');
            }
            if (!Schema::hasColumn('login_logs', 'is_proxy')) {
                $table->boolean('is_proxy')->default(false)->after('is_mobile_network');
            }
            if (!Schema::hasColumn('login_logs', 'is_hosting')) {
                $table->boolean('is_hosting')->default(false)->after('is_proxy');
            }
            if (!Schema::hasColumn('login_logs', 'is_vpn_suspected')) {
                $table->boolean('is_vpn_suspected')->default(false)->index()->after('is_hosting');
            }
            if (!Schema::hasColumn('login_logs', 'ip_timezone')) {
                $table->string('ip_timezone', 100)->nullable()->after('is_vpn_suspected');
            }
            if (!Schema::hasColumn('login_logs', 'client_timezone')) {
                $table->string('client_timezone', 100)->nullable()->after('ip_timezone');
            }
            if (!Schema::hasColumn('login_logs', 'device_fingerprint')) {
                $table->string('device_fingerprint', 64)->nullable()->index()->after('device_type');
            }
            if (!Schema::hasColumn('login_logs', 'device_model')) {
                $table->string('device_model', 150)->nullable()->after('device_fingerprint');
            }
            if (!Schema::hasColumn('login_logs', 'gpu_renderer')) {
                $table->string('gpu_renderer', 255)->nullable()->after('device_model');
            }
            if (!Schema::hasColumn('login_logs', 'cpu_cores')) {
                $table->unsignedSmallInteger('cpu_cores')->nullable()->after('gpu_renderer');
            }
            if (!Schema::hasColumn('login_logs', 'ram_gb')) {
                $table->decimal('ram_gb', 6, 2)->nullable()->after('cpu_cores');
            }
            if (!Schema::hasColumn('login_logs', 'screen_resolution')) {
                $table->string('screen_resolution', 50)->nullable()->after('ram_gb');
            }
            if (!Schema::hasColumn('login_logs', 'battery_level')) {
                $table->unsignedTinyInteger('battery_level')->nullable()->after('screen_resolution');
            }
            if (!Schema::hasColumn('login_logs', 'battery_charging')) {
                $table->boolean('battery_charging')->nullable()->after('battery_level');
            }
            if (!Schema::hasColumn('login_logs', 'network_type')) {
                $table->string('network_type', 30)->nullable()->after('battery_charging');
            }
            if (!Schema::hasColumn('login_logs', 'network_downlink')) {
                $table->decimal('network_downlink', 8, 2)->nullable()->after('network_type');
            }
            if (!Schema::hasColumn('login_logs', 'network_rtt')) {
                $table->unsignedInteger('network_rtt')->nullable()->after('network_downlink');
            }
            if (!Schema::hasColumn('login_logs', 'telemetry_data')) {
                $table->json('telemetry_data')->nullable()->after('user_agent');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('login_logs')) {
            return;
        }

        $columns = [
            'district',
            'isp',
            'org',
            'hostname',
            'is_mobile_network',
            'is_proxy',
            'is_hosting',
            'is_vpn_suspected',
            'ip_timezone',
            'client_timezone',
            'device_fingerprint',
            'device_model',
            'gpu_renderer',
            'cpu_cores',
            'ram_gb',
            'screen_resolution',
            'battery_level',
            'battery_charging',
            'network_type',
            'network_downlink',
            'network_rtt',
            'telemetry_data',
        ];

        $existing = array_filter($columns, fn (string $col) => Schema::hasColumn('login_logs', $col));

        if (!empty($existing)) {
            Schema::table('login_logs', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};
