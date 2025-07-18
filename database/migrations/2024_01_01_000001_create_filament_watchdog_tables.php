<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_integrity_checks', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('file_hash');
            $table->bigInteger('file_size');
            $table->timestamp('last_modified');
            $table->enum('status', ['clean', 'modified', 'deleted', 'new']);
            $table->text('changes')->nullable();
            $table->timestamps();

            $table->index('file_path');
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('malware_detections', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('threat_type');
            $table->string('signature_matched');
            $table->text('threat_details');
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['detected', 'quarantined', 'cleaned', 'false_positive']);
            $table->string('quarantine_path')->nullable();
            $table->timestamps();

            $table->index('threat_type');
            $table->index('risk_level');
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->string('user_id')->nullable();
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->text('event_details');
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical']);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('event_type');
            $table->index('user_id');
            $table->index('ip_address');
            $table->index('risk_level');
            $table->index('created_at');
        });

        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_type');
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['new', 'acknowledged', 'resolved', 'false_positive']);
            $table->json('metadata')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('acknowledged_by')->nullable();
            $table->string('resolved_by')->nullable();
            $table->timestamps();

            $table->index('alert_type');
            $table->index('severity');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('malware_detections');
        Schema::dropIfExists('file_integrity_checks');
    }
};