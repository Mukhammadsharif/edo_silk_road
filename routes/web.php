<?php

use App\Http\Controllers\Admin\DocLogsController;
use App\Http\Controllers\Admin\OnecOrderMappingController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\SubscriptionRequestController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Projects\BoardController;
use App\Http\Controllers\Projects\BoardLabelController;
use App\Http\Controllers\Projects\BoardListController;
use App\Http\Controllers\Projects\CardCommentController;
use App\Http\Controllers\Projects\CardController;
use App\Http\Controllers\LicenseActivationController;
use App\Http\Controllers\Api\ECP\SignDocImzoMobileApiController;
use App\Http\Controllers\DocController;
use App\Http\Controllers\ECP\SignDocImzoController;
use App\Http\Controllers\ECP\SignLoginController;
use App\Http\Controllers\EimzoTestController;
use App\Http\Controllers\ExecutionController;
use App\Http\Controllers\File\FileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Mobile\MobileSignController;
use App\Http\Controllers\Office\DepartmentJournalsController;
use App\Http\Controllers\Office\DocOrganizationsController;
use App\Http\Controllers\Office\DocumentController;
use App\Http\Controllers\Office\DocumentProxyController;
use App\Http\Controllers\Office\DocumentTemplatesController;
use App\Http\Controllers\Office\JournalController;
use App\Http\Controllers\Office\JournalTypesController;
use App\Http\Controllers\Office\OnlyOfficeController;
use App\Http\Controllers\PdfGenerateController;
use App\Http\Controllers\Projects\ChecklistController;
use App\Http\Controllers\ProtocolsController;
use App\Http\Controllers\QrCodeDocController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Storage\OrgStorageController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrganizationSwitchController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\UserPostController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\StatusListController;
use App\Http\Controllers\Admin\MenuListController;
use App\Http\Controllers\Admin\RoleMenuController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\LanguageLineController;
use App\Http\Controllers\HR\HrEmpController;
use App\Http\Controllers\HR\HrEmpEduController;
use App\Http\Controllers\HR\HrEmpExpController;
use App\Http\Controllers\HR\HrEmpFamController;
use App\Http\Controllers\HR\HrEmpOrderController;
use App\Http\Controllers\Admin\OnecIntegrationController;

use App\Http\Controllers\Protocol\ProtocolDocTypeController;
use App\Http\Controllers\Protocol\ProtocolEmpHrOrdersController;
use App\Http\Controllers\Comment\DocumentCommentController;

use App\Http\Controllers\ExternalDocFlowController;

Route::get('/edo', function () {
    return view('auth.login');
});

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/log-test', function () {
    Log::debug('Test log message');
    return 'Log written!';
});

Auth::routes();

Route::get('lang/{locale}', [HomeController::class, 'lang']);

Route::get('e-imzo/frontend-challenge',  [SignDocImzoController::class, 'frontChallenge']);
Route::post('e-imzo/backend-auth',  [SignDocImzoController::class, 'backendAuthLogin']);

Route::group(['middleware' => ['auth']], function() {

    Route::get('/edo', [HomeController::class, 'index'])->name('home');

    // Переключение между организациями (мульти-org membership)
    Route::post('/switch-organization/{org_id}', [OrganizationSwitchController::class, 'switch'])
        ->whereNumber('org_id')
        ->name('organization.switch');

    // ════════════════════════════════════════════════════════════════════
    // Хранилище организации (CRUD: Director / IT_Admin / Super_Admin)
    // ════════════════════════════════════════════════════════════════════
    Route::prefix('org-storage')->name('storage.')->group(function () {
        Route::get('/',                       [OrgStorageController::class, 'index'])->name('index');
        Route::get('/folder/{id}',            [OrgStorageController::class, 'showFolder'])->whereNumber('id')->name('folder.show');

        Route::post('/folder',                [OrgStorageController::class, 'createFolder'])->name('folder.create');
        Route::patch('/folder/{id}',          [OrgStorageController::class, 'renameFolder'])->whereNumber('id')->name('folder.rename');
        Route::delete('/folder/{id}',         [OrgStorageController::class, 'deleteFolder'])->whereNumber('id')->name('folder.delete');

        // Per-folder permissions management
        Route::get('/folder/{id}/permissions',                  [OrgStorageController::class, 'showPermissions'])->whereNumber('id')->name('folder.permissions.show');
        Route::post('/folder/{id}/permissions',                 [OrgStorageController::class, 'grantPermission'])->whereNumber('id')->name('folder.permissions.grant');
        Route::delete('/folder/{id}/permissions/{permId}',      [OrgStorageController::class, 'revokePermission'])->whereNumber('id')->whereNumber('permId')->name('folder.permissions.revoke');

        Route::post('/file',                  [OrgStorageController::class, 'uploadFile'])->name('file.upload');
        Route::patch('/file/{id}',            [OrgStorageController::class, 'renameFile'])->whereNumber('id')->name('file.rename');
        Route::delete('/file/{id}',           [OrgStorageController::class, 'deleteFile'])->whereNumber('id')->name('file.delete');
        Route::get('/file/{id}/download',     [OrgStorageController::class, 'downloadFile'])->whereNumber('id')->name('file.download');
        Route::get('/file/{id}/preview',      [OrgStorageController::class, 'previewFile'])->whereNumber('id')->name('file.preview');

        Route::get('/trash',                  [OrgStorageController::class, 'trash'])->name('trash');
        Route::post('/trash/folder/{id}/restore', [OrgStorageController::class, 'restoreFolder'])->whereNumber('id')->name('trash.folder.restore');
        Route::delete('/trash/folder/{id}',   [OrgStorageController::class, 'purgeFolder'])->whereNumber('id')->name('trash.folder.purge');
        Route::post('/trash/file/{id}/restore',   [OrgStorageController::class, 'restoreFile'])->whereNumber('id')->name('trash.file.restore');
        Route::delete('/trash/file/{id}',     [OrgStorageController::class, 'purgeFile'])->whereNumber('id')->name('trash.file.purge');
    });

    Route::get('charts/documents', [HomeController::class, 'chartDocuments'])->name('charts.documents');
    Route::post('get-menu-count', [HomeController::class, 'getMenuCount'])->name('get-menu-count');

    Route::prefix('profile')->group(function () {
        Route::get('my', [ProfileController::class, 'profile'])->name('profile.my');
        Route::post('pass-update', [ProfileController::class, 'passUpdate'])->name('my.pass-update');
        Route::post('personal-update', [ProfileController::class, 'personalUpdate'])->name('my.personal-update');

        Route::post('update-image', [ProfileController::class, 'updateImage'])->name('update-image');
        Route::get('del-user-img/{user_id}', [ProfileController::class, 'deleteProfileImage']);
    });

    Route::prefix('admin')->group(function () {

        Route::prefix('onec')->group(function () {
            Route::get('/',          [OnecIntegrationController::class, 'index'])->name('admin.onec.index');
            Route::post('/generate', [OnecIntegrationController::class, 'generate'])->name('admin.onec.generate');
            Route::post('/retry',    [OnecIntegrationController::class, 'retry'])->name('admin.onec.retry');
            // Соответствие кадровых журналов типам приказов 1С
            Route::get('/order-mapping',         [OnecOrderMappingController::class, 'index'])->name('admin.onec.mapping');
            Route::post('/order-mapping/update', [OnecOrderMappingController::class, 'update'])->name('admin.onec.mapping.update');
            Route::post('/order-mapping/reset',  [OnecOrderMappingController::class, 'reset'])->name('admin.onec.mapping.reset');
        });

        Route::prefix('organizations')->group(function () {
            Route::any('index', [DepartmentController::class, 'organizationsIndex'])->name('organizations.index');
            Route::any('create', [DepartmentController::class, 'organizationsCreate'])->name('organizations.create');
            Route::post('store', [DepartmentController::class, 'organizationsStore'])->name('organizations.store');
            Route::any('edit/{id}', [DepartmentController::class, 'organizationsEdit'])->name('organizations.edit');
            Route::any('update/{id}', [DepartmentController::class, 'organizationsUpdate'])->name('organizations.update');
            Route::delete('destroy/{id}', [DepartmentController::class, 'organizationsDestroy'])->name('organizations.destroy');
            Route::post('restore/{id}', [DepartmentController::class, 'organizationsRestore'])->name('organizations.restore');
            Route::any('send-request', [DepartmentController::class, 'sendRequest'])->name('organizations.send.request');
            Route::any('cancel-request', [DepartmentController::class, 'cancelRequest'])->name('organizations.cancel.request');
        });

        Route::prefix('departments')->group(function () {
            Route::any('index/{id}', [DepartmentController::class, 'departmentsIndex'])->name('departments.index');
            Route::any('create/{id}', [DepartmentController::class, 'departmentsCreate'])->name('departments.create');
            Route::post('store', [DepartmentController::class, 'departmentsStore'])->name('departments.store');
            Route::any('edit/{id}', [DepartmentController::class, 'departmentsEdit'])->name('departments.edit');
            Route::any('update/{id}', [DepartmentController::class, 'departmentsUpdate'])->name('departments.update');
        });

        // role
        Route::resource('roles', RoleController::class);
        Route::get('roles/{roleId}/delete', [RoleController::class, 'destroy']);
        Route::get('roles/{roleId}/give-permissions', [RoleController::class, 'addPermissionToRole']);
        Route::put('roles/{roleId}/give-permissions', [RoleController::class, 'givePermissionToRole']);

        Route::resource('permissions', PermissionController::class);
        Route::get('permissions/{permissionId}/delete', [PermissionController::class, 'destroy']);

        // user post
        Route::resource('user-posts', UserPostController::class);
        Route::any('super-user-posts/{orgId}', [UserPostController::class, 'superUserPosts'])->name('user-posts.super-user-posts');
        Route::get('user-posts/get-organization-departments/{orgId}', [UserPostController::class, 'getOrganizationDepartments'])->name('user-posts.get-organization-departments');
        Route::any('user-posts-search', [UserPostController::class, 'index'])->name('user-posts.search');

        Route::resource('users', UserController::class);
        Route::get('get-dep-pos/{id}', [UserController::class, 'getDepPos']);
        Route::get('user-check-email/{str}', [UserController::class, 'checkEmail']);
        Route::any('users-search', [UserController::class, 'index'])->name('user.search');
        Route::put('user/edit-personal-info/{id}/{hash}', [UserController::class, 'editPersonalInfo']);
        Route::put('user/edit-user-roles/{id}/{hash}', [UserController::class, 'editUserRoles']);
        Route::put('user/edit-user-dep-position/{id}/{hash}', [UserController::class, 'editUserDepartmentPosition']);
        Route::put('user/edit-user-login-password/{id}/{hash}', [UserController::class, 'editUserLoginPassword']);
        Route::put('user/edit-user-status/{id}/{hash}', [UserController::class, 'editUserStatus']);
        Route::put('user/edit-ecp-status/{id}/{hash}', [UserController::class, 'editEcpStatus']);
        Route::post('users/accept-request/{id}', [UserController::class, 'acceptRequest'])->name('users.accept-request');
        Route::post('users/reject-request/{id}', [UserController::class, 'rejectRequest'])->name('users.reject-request');

        // status name
        Route::resource('status-lists', StatusListController::class);
        Route::any('status-lists-index', [StatusListController::class, 'index'])->name('status-lists.index');
        Route::get('fetch-status-list', [StatusListController::class, 'fetchStatusList']);
        Route::get('search-status/{text}', [StatusListController::class, 'search']);

        // MENU LIST
        Route::resource('menu-list', MenuListController::class);
        Route::any('menu-list-search', [MenuListController::class, 'search']);

        // ROLE MENUS
        Route::resource('role-menus', RoleMenuController::class);
        Route::any('role-menus-search', [RoleMenuController::class, 'search']);
        Route::get('role-menus/user-role/{id}', [RoleMenuController::class, 'getParent']);

        // APP TEMPS
        //Route::resource('app-temps',AppTempsConroller::class);

        // DOC LOGS
        Route::any('doc-logs', [DocLogsController::class, 'index'])->name('doc-logs.index');
        Route::post('export-doc-logs',[DocLogsController::class,'exportDocLogs'])->name('export-doc-logs');

        Route::prefix('lang')->group(function () {
            Route::any('index', [LanguageLineController::class, 'index'])->name('language.index');
            Route::post('store', [LanguageLineController::class, 'store']);
            Route::get('fetch-lang', [LanguageLineController::class, 'fetchLanguage']);
            Route::get('edit-lang/{id}', [LanguageLineController::class, 'edit']);
            Route::put('update-lang/{id}', [LanguageLineController::class, 'update']);
            Route::delete('delete-lang/{id}', [LanguageLineController::class, 'destroy']);
            Route::get('search-lang/{id}', [LanguageLineController::class, 'search']);
        });

        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('settings-file/{key}', [SettingsController::class, 'logFile'])->name('settings.log-file');
        Route::get('settings-cache/{type}', [SettingsController::class, 'cacheClear'])->name('settings.cache.clear');
        Route::post('settings-set-demo', [SettingsController::class, 'updateDemoSetting'])->name('settings.set.demo');
        Route::post('settings-set-demo', [SettingsController::class, 'updateDemoSetting'])->name('settings.set.demo');
        Route::post('settings-set-env', [SettingsController::class, 'updateEnv'])->name('settings.set.env');
        Route::post('images/upload', [SettingsController::class, 'imageUpload'])->name('images.upload');

        Route::get('/get-positions/{id}', [UserController::class, 'getPositions'])->name('admin.get-positions');

        Route::resource('tariffs', TariffController::class)->names([
            'index'   => 'tariffs.index',
            'create'  => 'tariffs.create',
            'store'   => 'tariffs.store',
            'edit'    => 'tariffs.edit',
            'update'  => 'tariffs.update',
            'destroy' => 'tariffs.destroy',
        ]);

        // Litsenziya boshqaruvi (superadmin)
        Route::get('licenses', [LicenseController::class, 'index'])->name('admin.licenses.index');
        Route::get('licenses/create', [LicenseController::class, 'create'])->name('admin.licenses.create');
        Route::post('licenses', [LicenseController::class, 'store'])->name('admin.licenses.store');
        Route::get('licenses/{id}/download', [LicenseController::class, 'download'])->name('admin.licenses.download');
        Route::post('licenses/{id}/revoke', [LicenseController::class, 'revoke'])->name('admin.licenses.revoke');
    });

    Route::prefix('document-settings')->group(function () {
        Route::resource('document-type', JournalTypesController::class);
        Route::any('super-user-docs/{orgId}', [JournalTypesController::class, 'superUserOrgDocs'])->name('document-type.super-user-docs');
        Route::any('document-search', [JournalTypesController::class, 'index'])->name('document-type.search');
        Route::get('get-organization-journals/{orgId}', [JournalTypesController::class, 'getOrganizationJournals'])->name('document-type.get-organization-journals');
        Route::post('document-step-store', [JournalTypesController::class, 'stepStore'])->name('document.step.store');
        Route::any('document-step-roles/{step_id}/{j_type_id}', [JournalTypesController::class, 'stepRoles'])->name('document.step.roles');
        Route::post('document-control', [JournalTypesController::class, 'control'])->name('document.control');
        Route::any('app/document', [JournalTypesController::class, 'showapps'])->name('app-document');
        Route::any('app/document/{id}/edit', [JournalTypesController::class, 'editapps'])->name('app-document.edit');
        Route::any('app/document/{id}/update', [JournalTypesController::class, 'updateapp'])->name('app-document.update');

        // Protocol Doc type
        // except(['show']) обязателен: show() в контроллере не реализован, а
        // маршрут protocol-types/{protocol_type} перехватывает URL
        // /protocol-types/index (его генерирует route('protocol-types.index'),
        // потому что строкой ниже это имя переопределяется) и падает
        // BadMethodCallException: Method ...::show does not exist.
        Route::resource('protocol-types', ProtocolDocTypeController::class)->except(['show']);
        Route::any('protocol-types/index', [ProtocolDocTypeController::class, 'index'])->name('protocol-types.index');
        Route::any('protocol-types/super-user-docs/{orgId}', [ProtocolDocTypeController::class, 'superUserOrgDocs'])->name('protocol-types.super-user-docs');
        Route::post('prt-doc-sign-roles', [ProtocolDocTypeController::class, 'signRoles'])->name('prt.doc.sign.roles');
        Route::any('protocol-types/index', [ProtocolDocTypeController::class, 'index'])->name('protocol-types.index');
        Route::any('protocol-types/super-user-docs/{orgId}', [ProtocolDocTypeController::class, 'superUserOrgDocs'])->name('protocol-types.super-user-docs');
        Route::post('prt-doc-sign-roles', [ProtocolDocTypeController::class, 'signRoles'])->name('prt.doc.sign.roles');

        Route::resource('doc-org', DocOrganizationsController::class);
        Route::any('doc-org-search', [DocOrganizationsController::class, 'index'])->name('doc-org.search');
        Route::resource('protocol', DocOrganizationsController::class);
        Route::any('protocol-search', [DocOrganizationsController::class, 'index'])->name('protocol.search');

        Route::any('document-templates', [DocumentTemplatesController::class, 'index'])->name('document.templates');
        Route::any('document-templates/create', [DocumentTemplatesController::class, 'create'])->name('document.templates.create');
        Route::get('get-organization-journals-temps/{orgId}', [DocumentTemplatesController::class, 'getOrganizationJournals'])->name('document.templates-organization-journals-temps');
        Route::any('document-templates/store', [DocumentTemplatesController::class, 'store'])->name('document.templates.store');
        Route::any('document-templates/edit/{id}', [DocumentTemplatesController::class, 'edit'])->name('document.templates.edit');
        Route::any('document-templates/update/{id}', [DocumentTemplatesController::class, 'update'])->name('document.templates.update');

        Route::resource('department-journals', DepartmentJournalsController::class);
        Route::any('department-journals-search', [DepartmentJournalsController::class, 'index'])->name('department-journals.search');

    });

    Route::prefix('protocol')->group(function () {
        // Protocol Routes
        Route::any('{doc_type}', [ProtocolsController::class, 'index'])->name('prt-secretary');
        Route::get('create-only/{doc_type}', [ProtocolsController::class, 'createOnly'])->name('prt-secretary-create-only');
        Route::get('create/{doc_type}/{document_key}', [ProtocolsController::class, 'create'])->name('prt-secretary-create');
        Route::post('store/{doc_type}', [ProtocolsController::class, 'store'])->name('prt-secretary-store');
        Route::get('view/{id}/{doc_hash}', [ProtocolsController::class, 'view'])->name('prt-secretary-view');
        Route::get('edit-only/{id}/{doc_hash}', [ProtocolsController::class, 'editOnly'])->name('prt-secretary-edit-only');
        Route::get('edit/{id}/{doc_hash}/{document_key}', [ProtocolsController::class, 'edit'])->name('prt-secretary-edit');
        Route::get('delete/{id}', [ProtocolsController::class, 'delete'])->name('prt-secretary-delete');
        Route::put('update/{id}/{doc_hash}', [ProtocolsController::class, 'update'])->name('prt-secretary-update');
        Route::get('send-to-members/{id}/{doc_hash}', [ProtocolsController::class, 'sendToMembers'])->name('prt-secretary-send-members');
        Route::get('get-protocol-users/{id}', [ProtocolsController::class, 'getDocTypeUsers']);
        Route::get('search-hr-emp/{doc_hash}', [ProtocolsController::class, 'searchHrEmp'])->name('protocol.hr-emp');

        // HR EMP
        Route::any('emp/hr-orders', [ProtocolEmpHrOrdersController::class, 'index'])->name('prt-emp-hr-orders');
        Route::get('emp/hr-order/view/{id}/{doc_hash}', [ProtocolEmpHrOrdersController::class, 'view'])->name('prt-emp-hr-order-view');

        Route::prefix('file')->group(function () {
            Route::get('pre-view/{id}', [FileController::class, 'protocolDocFilePreView'])->name('prt-document-file.pre-view');
            Route::get('download/{id}', [FileController::class, 'protocolDocFileDownload'])->name('prt-document-file.download');
            Route::get('delete/{id}', [FileController::class, 'protocolDocFileDelete']);
        });

    });

    Route::prefix('office')->group(function () {
        Route::get('document/create/{type}', [DocumentController::class, 'documentCreate']);
        Route::get('document/form/{type}', [DocumentController::class, 'loadForm']);
        Route::post('document-report-form-store', [DocumentController::class, 'documentReportFormEntryStore'])->name('office.doc.report.form.store');
        Route::delete('document-report-form-delete/{id}', [DocumentController::class, 'documentReportFormEntryDelete'])->name('office.doc.report.form.delete');
        Route::post('store-doc', [DocumentController::class, 'store'])->name('office.doc.store');
        Route::post('resolve-related-link', [DocumentController::class, 'resolveRelatedLink'])->name('office.doc.resolve-related-link');
        Route::get('edit-doc/{type}/{j_id}/{hash}', [DocumentController::class, 'documentEdit'])->name('office.doc.edit');
        Route::post('update-doc/{j_id}/{hash}', [DocumentController::class, 'update'])->name('office.doc.update');
        Route::get('get-journals/{j_type_id}', [DocumentController::class, 'getJournals']);
        Route::get('get-emp-app-template/{j_id}', [DocumentController::class, 'getEmpAppTemplate'])->name('get-emp-app-template');
        Route::get('get-journal-number/{j_id}', [DocumentController::class, 'getJournalNumber']);
        Route::get('get-execution-users/{j_id}', [DocumentController::class, 'getExecutionUsers']);
        Route::get('get-execution-selected-users/{j_id}/{id}', [DocumentController::class, 'getExecutionSelectedUsers']);
        Route::get('get-doc-organizations/{j_id}', [DocumentController::class, 'getDocOrganizations']);
        Route::get('get-selected-doc-organizations/{j_id}/{id}', [DocumentController::class, 'getSelectedDocOrganizations']);
        Route::get('get-document-text/{j_id}', [DocumentController::class, 'getDocumentText']);

        Route::get('/storage/{filename}', function ($filename) {
            $path = storage_path('app/public/' . $filename);
            if (!file_exists($path)) {
                abort(404);
            }
            return response()->file($path);
        });
        Route::get('agreement-sheet/{id}/{hash}', [JournalController::class, 'agreementSheet'])
            ->name('office.agreement-sheet');
        Route::any('document-create', [OnlyOfficeController::class, 'create'])->name('document-only-office.create');
        Route::get('/doc/view', [OnlyOfficeController::class, 'docView']);
        Route::get('download-pdf/{onlyDocId}', [OnlyOfficeController::class, 'convertToPDF'])->name('download-only-office.pdf');
        Route::any('edit-doc-only/{j_id}/{hash}', [OnlyOfficeController::class, 'edit'])->name('office.doc.edit-only');
        Route::post('onlyoffice/forcesave', [OnlyOfficeController::class, 'forceSave'])->name('office.onlyoffice.forcesave');

        Route::get('view-inbox-doc/{id}/{hash}', [JournalController::class, 'viewInbox'])->name('office.view-inbox-doc');
        Route::get('view-sent-doc/{id}/{hash}', [JournalController::class, 'viewSent'])->name('office.view-sent-doc');
        Route::get('view-work-doc/{id}/{hash}', [JournalController::class, 'viewWork'])->name('office.view-work-doc');
        Route::get('view-free-sign/{id}/{hash}', [JournalController::class, 'viewFreeSign'])->name('office.work.free-sign');
        Route::post('free-sign/{document}/save', [DocumentController::class, 'saveFromEditor'])
            ->name('free-sign.save');
        Route::get('free-sign/{document}/status', [DocumentController::class, 'documentStatus'])
            ->name('free-sign.status');

        // Версии PDF: история подписания, просмотр и скачивание.
        Route::get('free-sign/{journal}/versions', [\App\Http\Controllers\FreeSign\FreeSignVersionController::class, 'index'])
            ->name('free-sign.versions');
        Route::get('free-sign/version/{fileId}', [\App\Http\Controllers\FreeSign\FreeSignVersionController::class, 'show'])
            ->name('free-sign.version.show');
        Route::get('free-sign/{journal}/download/{which?}', [\App\Http\Controllers\FreeSign\FreeSignVersionController::class, 'download'])
            ->name('free-sign.download');
        Route::post('free-sign/login-sign', [\App\Http\Controllers\ECP\SignDocImzoController::class, 'loginSignFreeSign'])
            ->name('free-sign.login-sign');
        // Резолюция и исполнение внутри free-sign редактора.
        Route::post('free-sign/{journal}/resolution', [\App\Http\Controllers\FreeSign\FreeSignResolutionController::class, 'store'])
            ->name('free-sign.resolution.store');
        Route::post('free-sign/reply/{step}', [\App\Http\Controllers\FreeSign\FreeSignResolutionController::class, 'reply'])
            ->name('free-sign.reply');
        Route::get('free-sign/performers/search', [\App\Http\Controllers\FreeSign\FreeSignResolutionController::class, 'searchPerformers'])
            ->name('free-sign.performers.search');

        Route::any('work-docs-index', [JournalController::class, 'workDocs'])->name('office.work-docs');
        Route::any('inbox-docs-index', [JournalController::class, 'inboxDocs'])->name('office.inbox-docs');
        Route::any('outgoing-docs-index', [JournalController::class, 'outgoingDocs'])->name('office.outgoing-docs');
        Route::get('del-doc/{doc_id}', [JournalController::class, 'deleteDocument'])->name('office.del-doc');

    });

    Route::prefix('file')->group(function () {
        Route::get('pre-view/{id}', [FileController::class, 'preView'])->name('file.pre-view');
        Route::get('download/{id}', [FileController::class, 'fileDownload'])->name('file.download');
        Route::get('r-pre-view/{id}', [FileController::class, 'preViewReply'])->name('r-file.pre-view');
        Route::get('r-download/{id}', [FileController::class, 'downloadReply'])->name('r-file.download');
        Route::get('del/{id}', [FileController::class, 'fileDelete'])->name('file.delete');
    });

    Route::prefix('documents')->group(function () {
        Route::any('for-execution', [ExecutionController::class, 'forExecutionIndex'])->name('documents.for-execution.index');
        Route::any('for-sign', [ExecutionController::class, 'forSignIndex'])->name('documents.for-sign.index');
        Route::any('for-approval', [ExecutionController::class, 'forApprovalIndex'])->name('documents.for-approval.index');
        Route::any('for-control', [ExecutionController::class, 'forControlIndex'])->name('documents.for-control.index');
        Route::any('for-confirm', [ExecutionController::class, 'forConfirmIndex'])->name('documents.for-confirm.index');

        Route::any('view-sign-document/{j_id}/{doc_hash}', [ExecutionController::class, 'viewSignDocument'])->name('document.sign.view');
        Route::any('view-approval-document/{j_id}/{doc_hash}', [ExecutionController::class, 'viewApprovalDocument'])->name('document.approval.view');
        Route::any('view-control-document/{j_id}/{doc_hash}', [ExecutionController::class, 'viewControlDocument'])->name('document.control.view');
        Route::any('view-confirm-document/{j_id}/{doc_hash}', [ExecutionController::class, 'viewConfirmDocument'])->name('document.confirm.view');
        Route::any('view-exec-document/{j_id}/{doc_hash}', [ExecutionController::class, 'viewExecDocument'])->name('document.exec.view');

        Route::any('confirm/{j_id}', [ExecutionController::class, 'confirmReject'])->name('confirm-reject-doc');
        Route::post('create-resolution/{j_id}', [ExecutionController::class, 'createResolution'])->name('document.create-resolution');
        Route::post('/res-performer/search', [ExecutionController::class, 'searchResPerformers'])->name('res-performer.search');
        Route::get('control-send-to-edit/{j_id}', [ExecutionController::class, 'controlSendToEdit'])->name('document.send.to.edit');
        Route::post('change-resolution/{j_id}', [ExecutionController::class, 'changeResolution'])->name('document.change-resolution');
        Route::post('sign-resolution/{j_id}', [ExecutionController::class, 'signResolution'])->name('document.sign-resolution');
        Route::any('reply-document/{step_id}', [ExecutionController::class, 'replyDocument'])->name('document.reply-document');
        Route::get('delete-reply-document/{id}', [ExecutionController::class, 'deleteReplyComment'])->name('delete.reply-comment');
        Route::any('add-performers/{j_id}', [ExecutionController::class, 'addPerformers'])->name('document.add-performers');
        Route::get('execute-document/{exec_id}', [ExecutionController::class, 'executeWorkDoc'])->name('execute.work.document');
    });

    Route::prefix('hr')->group(function () {
        // ORDER
        Route::resource('hr-order', HrEmpOrderController::class);
        // EDUCATION
        Route::resource('education', HrEmpEduController::class);
        // FAMILY
        Route::resource('family', HrEmpFamController::class);
        // experiences
        Route::resource('exp', HrEmpExpController::class);

        //Route::resource('emp', HrEmpController::class);
        Route::any('emp', [HrEmpController::class, 'index'])->name('hr.emp');
        Route::get('emp/create', [HrEmpController::class, 'create'])->name('hr.emp.create');
        Route::post('emp/store', [HrEmpController::class, 'store'])->name('hr.emp.store');
        Route::get('emp/view/{id}/{user_hash}', [HrEmpController::class, 'view'])->name('hr.emp.view');

        Route::get('emp/info-view/{id}/{user_hash}', [HrEmpController::class, 'infoView'])->name('hr.emp.info.view');
        Route::put('emp/info-update/{id}/{user_hash}', [HrEmpController::class, 'infoUpdate'])->name('hr.emp.info-update');

        Route::get('emp/order-view/{id}/{user_hash}', [HrEmpController::class, 'orderView'])->name('hr.emp.order.view');

        //Route::get('emp/work-view/{id}/{user_hash}', [HrEmpController::class, 'workView'])->name('hr.emp.work.view');
        //Route::put('emp/work-update/{id}/{user_hash}', [HrEmpController::class, 'workUpdate'])->name('hr.emp.work-update');

        Route::get('emp/edu-view/{id}/{user_hash}', [HrEmpController::class, 'eduView'])->name('hr.emp.edu.view');

        Route::get('emp/fam-view/{id}/{user_hash}', [HrEmpController::class, 'famView'])->name('hr.emp.fam.view');

        Route::get('emp/exp-view/{id}/{user_hash}', [HrEmpController::class, 'expView'])->name('hr.emp.exp.view');
    });

    Route::prefix('pdf')->group(function () {
        Route::get('work-doc/{doc_id}/{doc_hash}', [PdfGenerateController::class, 'workingDocument'])->name('pdf.work-doc');
        Route::get('inbox-doc/{doc_id}/{doc_hash}', [PdfGenerateController::class, 'inboxDocument'])->name('pdf.inbox-doc');
        Route::get('sent-doc/{doc_id}/{doc_hash}', [PdfGenerateController::class, 'sentDocument'])->name('pdf.sent-doc');
    });

    Route::post('ecp-sign-doc', [SignDocImzoController::class, 'signDocuments']);
    Route::post('login-sign-doc', [SignLoginController::class, 'signDocuments'])->name('login.sign.doc');

    Route::prefix('doc-controller')->group(function () {
        Route::any('protocols', [DocController::class, 'protocols'])->name('doc-controller.protocols');
        Route::any('work-docs', [DocController::class, 'workDocs'])->name('doc-controller.work-docs');
        Route::any('delete/work-document/{id}', [DocController::class, 'delWorkDoc'])->name('del.work-docs');
        Route::get('view-work-doc/{id}/{hash}', [DocController::class, 'actionRoute'])->name('doc-controller.action-route');
        Route::get('protocols-view/{id}/{hash}', [DocController::class, 'protocolView'])->name('doc-controller.protocol-view');
        Route::any('delete/protocol-document/{id}', [DocController::class, 'delProtocol'])->name('del-protocol');
        Route::any('public-protocols', [DocController::class, 'publicProtocols'])->name('doc-controller.public-protocols');
        Route::get('work-docs/filter-data', [DocController::class, 'getFilterDataByOrg'])->name('doc-controller.work-docs.filter-data');
        Route::get('protocols/filter-data', [DocController::class, 'getProtocolFilterDataByOrg'])->name('doc-controller.protocols.filter-data');
    });


    Route::prefix('comment')->group(function () {
        Route::get('counts', [DocumentCommentController::class, 'getCounts'])->name('doc.comment.counts');
        Route::get('get/{id}', [DocumentCommentController::class, 'getComments'])->name('doc.comment.get');
        Route::get('view/{doc_id}/{doc_type}', [DocumentCommentController::class, 'viewComment'])->name('doc.comment.view');
        Route::post('send/{id}/{doc_hash}', [DocumentCommentController::class, 'sendComment'])->name('doc.comment.send');
        Route::post('search', [DocumentCommentController::class, 'searchComments'])->name('doc.comment.search');
    });

    Route::any('org-structure', [DepartmentController::class, 'orgStructure'])->name('org-structure');
    Route::get('/org-structure-data', [DepartmentController::class, 'orgStructureData']);

    Route::prefix('messages')->group(function () {
        Route::get('index', [MessageController::class, 'index'])->name('messages.index');
        Route::post('store', [MessageController::class, 'store'])->name('messages.store');
        Route::any('list/{type}', [MessageController::class, 'list'])->name('messages.list');
        Route::get('show/{id}/{hash}', [MessageController::class, 'view'])->name('messages.show');
        Route::post('forward/{messageId}', [MessageController::class, 'forward'])->name('messages.forward');
        Route::post('reply/message/{messageId}', [MessageController::class, 'reply'])->name('messages.reply');
//        Route::get('search-users', [MessageController::class, 'searchUsers'])->name('messages.search-users');
        Route::get('delete/{id}', [MessageController::class, 'delete'])->name('messages.delete');
        Route::get('users-work-history/active', [MessageController::class, 'getActiveUsers'])->name('users.work-history.active');

        Route::prefix('file')->group(function () {
            Route::get('pre-view/{id}', [MessageController::class, 'preView'])->name('messages.file.pre-view');
            Route::get('download/{id}', [MessageController::class, 'fileDownload'])->name('messages.file.download');
            Route::get('del/{id}', [MessageController::class, 'fileDelete'])->name('messages.file.delete');
        });
    });

    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
    Route::post('/pricing/subscribe/{tariff}', [PricingController::class, 'subscribe'])->name('pricing.subscribe');

    // Litsenziyani faollashtirish (mijoz: direktor / IT admin)
    Route::get('/license/activate', [LicenseActivationController::class, 'index'])->name('license.activate');
    Route::post('/license/activate', [LicenseActivationController::class, 'activate'])->name('license.activate.submit');

    Route::get('/subscription-requests/search-organizations', [SubscriptionRequestController::class, 'searchOrganizations'])
        ->name('admin.subscription-requests.search-organizations');

    Route::get('/subscription-requests/org-users/{orgId}', [SubscriptionRequestController::class, 'orgUsers'])
        ->whereNumber('orgId')->name('admin.subscription-requests.org-users');

    Route::get('/subscription-requests', [SubscriptionRequestController::class, 'index'])
        ->name('admin.subscription-requests');

    Route::post('/subscription-requests', [SubscriptionRequestController::class, 'store'])
        ->name('admin.subscription-requests.store');

    Route::post('/subscription-requests/{id}/approve', [SubscriptionRequestController::class, 'approve'])
        ->name('admin.subscription-requests.approve');

    Route::post('/subscription-requests/{id}/reject', [SubscriptionRequestController::class, 'reject'])
        ->name('admin.subscription-requests.reject');

    Route::get('/subscription-requests/{id}/edit', [SubscriptionRequestController::class, 'edit'])
        ->name('admin.subscription-requests.edit');

    Route::put('/subscription-requests/{id}', [SubscriptionRequestController::class, 'update'])
        ->name('admin.subscription-requests.update');
});

Route::get('p/{doc_code}', [QrCodeDocController::class, 'viewProtocolDocument']);
Route::get('d/{doc_hash}', [QrCodeDocController::class, 'viewWorkDocument']);
Route::post('/confirm-key', [QrCodeDocController::class, 'confirmKey'])->name('confirm-key');
Route::post('/create-key', [QrCodeDocController::class, 'createQrKeyCode'])->name('create-key');

Route::get('/send-telegram-notification/{user_id}', [TelegramWebhookController::class, 'sendTelegramNotification']);

Route::get('/proxy-document/{path}', [DocumentProxyController::class, 'show'])
    ->name('document.proxy')
    ->where('path', '[A-Za-z0-9+/=]+');

Route::get('/eimzo-sign-mobile', [EimzoTestController::class, 'index'])
    ->name('eimzo.test');
Route::get('/privacy-policy', [EimzoTestController::class, 'privacy'])
    ->name('eimzo.privacy');

Route::post('/frontend/mobile/upload', [SignDocImzoMobileApiController::class, 'mobileUpload']);

Route::prefix('mobile')->group(function () {
    Route::get('/sign/{token}', [MobileSignController::class, 'show'])->name('mobile.sign');
    Route::post('/sign/{token}/complete', [MobileSignController::class, 'complete'])->name('mobile.sign.complete');
});

Route::get('/mobile/redirect', function(\Illuminate\Http\Request $request) {
    $url = $request->query('url', '');
    if (!str_starts_with($url, 'eimzo://')) abort(400);
    return view('mobile.redirect', ['url' => $url]);
})->name('mobile.redirect');

Route::get('/telegram-link/eimzo', function(\Illuminate\Http\Request $request) {
    $token = $request->query('token', '');
    if (!is_string($token) || $token === '' || strlen($token) > 128) {
        abort(400, 'Invalid token');
    }

    $payload = \Illuminate\Support\Facades\Cache::get("tg_eimzo_link:{$token}");
    if (!$payload || empty($payload['chat_id'])) {
        return view('mobile.eimzo-link', [
            'link_token' => null,
            'expired'    => true,
        ]);
    }

    return view('mobile.eimzo-link', [
        'link_token' => $token,
        'expired'    => false,
    ]);
})->name('telegram.link.eimzo');


Route::middleware('auth')->group(function () {
    Route::resource('boards', BoardController::class);

    Route::post('boards/{board}/lists', [BoardListController::class, 'store'])->name('lists.store');
    Route::patch('lists/{list}', [BoardListController::class, 'update'])->name('lists.update');
    Route::patch('lists/{list}/move', [BoardListController::class, 'move'])->name('lists.move');
    Route::delete('lists/{list}', [BoardListController::class, 'destroy'])->name('lists.destroy');

    Route::post('lists/{list}/cards', [CardController::class, 'store'])->name('cards.store');
    Route::get('cards/{card}', [CardController::class, 'show'])->name('cards.show');
    Route::patch('cards/{card}', [CardController::class, 'update'])->name('cards.update');
    Route::patch('cards/{card}/move', [CardController::class, 'move'])->name('cards.move');
    Route::delete('cards/{card}', [CardController::class, 'destroy'])->name('cards.destroy');

    Route::post('cards/{card}/comments', [CardCommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [CardCommentController::class, 'destroy'])->name('comments.destroy');

    // Карточка целиком (с связями)
    Route::get('cards/{card}/full', [CardController::class, 'full'])->name('cards.full');

    // Участники
    Route::post('cards/{card}/members', [CardController::class, 'addMember'])->name('cards.members.add');
    Route::delete('cards/{card}/members/{user}', [CardController::class, 'removeMember'])->name('cards.members.remove');

    // Чек-листы
    Route::post('cards/{card}/checklists', [ChecklistController::class, 'store'])->name('checklists.store');
    Route::delete('checklists/{checklist}', [ChecklistController::class, 'destroy'])->name('checklists.destroy');
    Route::post('checklists/{checklist}/items', [ChecklistController::class, 'addItem'])->name('checklists.items.add');
    Route::patch('checklist-items/{item}', [ChecklistController::class, 'toggleItem'])->name('checklist-items.toggle');
    Route::delete('checklist-items/{item}', [ChecklistController::class, 'destroyItem'])->name('checklist-items.destroy');

    // Список пользователей для назначения
    Route::get('board-users', [CardController::class, 'boardUsers'])->name('board.users');

    Route::get('boards/{board}/members', [BoardController::class, 'members'])->name('boards.members');
    Route::post('boards/{board}/members', [BoardController::class, 'addMember'])->name('boards.members.add');
    Route::delete('boards/{board}/members/{user}', [BoardController::class, 'removeMember'])->name('boards.members.remove');
    Route::get('board-users-all', [CardController::class, 'allOrgUsers'])->name('board.users.all');
    // метки доски (CRUD)
    Route::get('boards/{board}/labels', [BoardLabelController::class, 'index'])->name('labels.index');
    Route::post('boards/{board}/labels', [BoardLabelController::class, 'store'])->name('labels.store');
    Route::patch('board-labels/{label}', [BoardLabelController::class, 'update'])->name('labels.update');
    Route::delete('board-labels/{label}', [BoardLabelController::class, 'destroy'])->name('labels.destroy');

    // метки на карточке (переключение)
    Route::post('cards/{card}/labels/{label}', [CardController::class, 'attachLabel'])->name('cards.labels.attach');
    Route::delete('cards/{card}/labels/{label}', [CardController::class, 'detachLabel'])->name('cards.labels.detach');

    Route::prefix('external-docs')->name('external-docs.')->middleware(['auth', \App\Http\Middleware\EnsureDidoxAccess::class])->group(function () {
        Route::get('/index',                     [ExternalDocFlowController::class, 'index'])->name('index');
        Route::get('/outgoing',             [ExternalDocFlowController::class, 'outgoing'])->name('outgoing');
        Route::get('/create',               [ExternalDocFlowController::class, 'createForm'])->name('create');
        Route::post('/create',              [ExternalDocFlowController::class, 'storeOutgoing'])->name('store');
        Route::get('/approvals',            [ExternalDocFlowController::class, 'approvals'])->name('approvals');
        Route::post('/auth/password',       [ExternalDocFlowController::class, 'loginPassword'])->name('auth.password');
        Route::post('/auth/ecp',            [ExternalDocFlowController::class, 'loginEcp'])->name('auth.ecp');
        Route::post('/sync',                [ExternalDocFlowController::class, 'syncNow'])->name('sync');
        Route::get('/{id}',                 [ExternalDocFlowController::class, 'show'])->whereNumber('id')->name('show');
        Route::get('/{id}/print',           [ExternalDocFlowController::class, 'printForm'])->whereNumber('id')->name('print');
        // Печатная форма и вложения байтами с НАШЕГО домена — для инлайн-рендера
        // через pdf.js и склейки в один файл (storage-ссылки Didox без CORS).
        Route::get('/{id}/print-pdf',       [ExternalDocFlowController::class, 'printPdf'])->whereNumber('id')->name('print-pdf');
        Route::get('/{id}/attachment-raw/{didoxId?}', [ExternalDocFlowController::class, 'attachmentRaw'])->whereNumber('id')->name('attachment-raw');
        Route::get('/{id}/file/{didoxId?}', [ExternalDocFlowController::class, 'file'])->whereNumber('id')->name('file');
        Route::post('/{id}/send-to-review', [ExternalDocFlowController::class, 'sendToReview'])->whereNumber('id')->name('send-review');
        Route::post('/{id}/decide',         [ExternalDocFlowController::class, 'decide'])->whereNumber('id')->name('decide');
        Route::post('/{id}/refresh-status', [ExternalDocFlowController::class, 'refreshStatus'])->whereNumber('id')->name('refresh-status');

        // Web-прокси подписи (E-IMZO поток из карточки, работают на web-сессии)
        Route::post('/timestamp',            [ExternalDocFlowController::class, 'timestamp'])->name('timestamp');
        Route::post('/signature-join',       [ExternalDocFlowController::class, 'signatureJoin'])->name('signature-join');
        Route::get('/{id}/document-base64',  [ExternalDocFlowController::class, 'documentBase64'])->whereNumber('id')->name('document-base64');
        Route::get('/{id}/outgoing-sign-data', [ExternalDocFlowController::class, 'outgoingSignData'])->whereNumber('id')->name('outgoing-sign-data');
        Route::get('/{id}/tosign-value',     [ExternalDocFlowController::class, 'toSignValue'])->whereNumber('id')->name('tosign-value');
        Route::post('/{id}/tosign',          [ExternalDocFlowController::class, 'toSign'])->whereNumber('id')->name('tosign');
        Route::post('/{id}/sign',            [ExternalDocFlowController::class, 'sign'])->whereNumber('id')->name('sign');
    });
});

/**
 * Handoff из Mini App в Web — открыть OnlyOffice editor для создания документа.
 *
 * Юзер на телефоне набрал часть формы в Mini App, нажал «Открыть на компьютере».
 * Mini App вызвал POST /api/telegram/create/web-editor и получил такой URL.
 * Здесь мы достаём draft из cache, проверяем что юзер тот же, и редиректим
 * на форму создания с pre-fill через query параметр draft.
 *
 * Требует логин в обычном web-интерфейсе (через auth middleware ниже).
 */
Route::middleware(['auth'])->group(function() {
    Route::get('/telegram/handoff-create', function(\Illuminate\Http\Request $request) {
        $draft = $request->query('draft', '');
        if (!is_string($draft) || $draft === '' || strlen($draft) > 128) {
            abort(400, 'Invalid draft token');
        }

        $payload = \Illuminate\Support\Facades\Cache::get("tg_create_draft:{$draft}");
        if (!$payload || empty($payload['user_id'])) {
            return redirect()->route('home')
                ->with('error', 'Ссылка на черновик устарела или не найдена. Создайте документ заново.');
        }

        // Проверка владельца: draft создан конкретным юзером, его и должен открыть
        if ((int) $payload['user_id'] !== (int) \Illuminate\Support\Facades\Auth::id()) {
            return redirect()->route('home')
                ->with('error', 'Этот черновик принадлежит другому пользователю.');
        }

        $data = $payload['data'] ?? [];
        $type = $data['type'] ?? 'internal';
        if (!in_array($type, ['incoming', 'outgoing', 'internal'], true)) {
            $type = 'internal';
        }

        // Прокидываем драфт-токен в URL — фронт-форма прочитает и пресет поля.
        return redirect("/office/document/create/{$type}?prefill=" . urlencode($draft));
    })->name('telegram.handoff.create');
});

// ── Группы согласующих (Super_Admin / IT_Admin) ──
require __DIR__.'/approval_groups.php';
