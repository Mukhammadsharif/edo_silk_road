<?php

/**
 * Маршруты «Группы согласующих».
 *
 * Подключение: в конце файла routes/web.php добавьте одну строку:
 *     require __DIR__.'/approval_groups.php';
 *
 * (либо перенесите содержимое группы внутрь существующей
 *  Route::prefix('admin')->group(...) в web.php — тогда этот файл не нужен).
 */

use App\Http\Controllers\Admin\ApprovalGroupController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('approval-groups',              [ApprovalGroupController::class, 'index'])->name('approval-groups.index');
    Route::get('approval-groups/create',       [ApprovalGroupController::class, 'create'])->name('approval-groups.create');
    Route::post('approval-groups',             [ApprovalGroupController::class, 'store'])->name('approval-groups.store');
    Route::get('approval-groups/{id}/edit',    [ApprovalGroupController::class, 'edit'])->whereNumber('id')->name('approval-groups.edit');
    Route::put('approval-groups/{id}',         [ApprovalGroupController::class, 'update'])->whereNumber('id')->name('approval-groups.update');
    Route::delete('approval-groups/{id}',      [ApprovalGroupController::class, 'destroy'])->whereNumber('id')->name('approval-groups.destroy');

    // AJAX: пользователи организации (для Super_Admin при смене организации)
    Route::get('approval-groups/org-users/{orgId}', [ApprovalGroupController::class, 'getOrgUsers'])->whereNumber('orgId')->name('approval-groups.org-users');
});
