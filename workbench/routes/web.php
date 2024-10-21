<?php

use BradieTilley\AuditLogs\AuditLogRecorder;
use Illuminate\Support\Facades\Route;
use Workbench\App\Models\User;

Route::get('request-logging-test/{user}', function (User $user, AuditLogRecorder $recorder) {
    $recorder->record($user, action: 'Done something');

    return response()->json([
        //
    ]);
});
