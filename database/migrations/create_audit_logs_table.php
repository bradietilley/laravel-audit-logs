<?php

use BradieTilley\AuditLogs\AuditLogConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignIdFor(AuditLogConfig::getUserModel(), 'user_id')->nullable();
            $table->nullableMorphs('model', 'model');

            $table->enum('type', [
                AuditLogConfig::getAuditLogModel()::TYPE_AUDIT,
                AuditLogConfig::getAuditLogModel()::TYPE_ACTIVITY,
            ]);
            $table->text('action');
            $table->text('data');
            $table->binary('ip', 16)->index();

            $table->timestamp('created_at', 3);
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
