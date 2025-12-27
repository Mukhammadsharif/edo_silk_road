<?php

use App\Http\Controllers\Admin\DocLogsController;
use App\Http\Controllers\DocController;
use App\Http\Controllers\ECP\SignDocImzoController;
use App\Http\Controllers\ECP\SignLoginController;
use App\Http\Controllers\ExecutionController;
use App\Http\Controllers\File\FileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Office\DepartmentJournalsController;
use App\Http\Controllers\Office\DocOrganizationsController;
use App\Http\Controllers\Office\DocumentController;
use App\Http\Controllers\Office\DocumentTemplatesController;
use App\Http\Controllers\Office\JournalController;
use App\Http\Controllers\Office\JournalTypesController;
use App\Http\Controllers\Office\OnlyOfficeController;
use App\Http\Controllers\PdfGenerateController;
use App\Http\Controllers\ProtocolsController;
use App\Http\Controllers\QrCodeDocController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
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

use App\Http\Controllers\Protocol\ProtocolDocTypeController;
use App\Http\Controllers\Protocol\ProtocolEmpHrOrdersController;
use App\Http\Controllers\Comment\DocumentCommentController;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/log-test', function () {
    Log::debug('Test log message');
    return 'Log written!';
});

Auth::routes();

Route::get('lang/{locale}', [HomeController::class, 'lang']);

Route::group(['middleware' => ['auth']], function() {

    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('charts/documents', [HomeController::class, 'chartDocuments'])->name('charts.documents');
    Route::post('get-menu-count', [HomeController::class, 'getMenuCount'])->name('get-menu-count');

    Route::prefix('profile')->group(function () {
        Route::get('my', [ProfileController::class, 'profile']);
        Route::post('pass-update', [ProfileController::class, 'passUpdate'])->name('my.pass-update');
        Route::post('personal-update', [ProfileController::class, 'personalUpdate'])->name('my.personal-update');

        Route::post('update-image', [ProfileController::class, 'updateImage'])->name('update-image');
        Route::get('del-user-img/{user_id}', [ProfileController::class, 'deleteProfileImage']);
    });

    Route::prefix('admin')->group(function () {
        // role
        Route::resource('departments', DepartmentController::class);
        Route::any('departments-search', [DepartmentController::class, 'index'])->name('departments.search');

        // role
        Route::resource('roles', RoleController::class);
        Route::get('roles/{roleId}/delete', [RoleController::class, 'destroy']);
        Route::get('roles/{roleId}/give-permissions', [RoleController::class, 'addPermissionToRole']);
        Route::put('roles/{roleId}/give-permissions', [RoleController::class, 'givePermissionToRole']);

        Route::resource('permissions', PermissionController::class);
        Route::get('permissions/{permissionId}/delete', [PermissionController::class, 'destroy']);

        // user post
        Route::resource('user-posts', UserPostController::class);
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
    });

    Route::prefix('document-settings')->group(function () {
        Route::resource('document-type', JournalTypesController::class);
        Route::any('document-search', [JournalTypesController::class, 'index'])->name('document-type.search');
        Route::post('document-step-store', [JournalTypesController::class, 'stepStore'])->name('document.step.store');
        Route::any('document-step-roles/{step_id}/{j_type_id}', [JournalTypesController::class, 'stepRoles'])->name('document.step.roles');
        Route::post('document-control', [JournalTypesController::class, 'control'])->name('document.control');
        Route::any('app/document', [JournalTypesController::class, 'showapps'])->name('app-document');
        Route::any('app/document/{id}/edit', [JournalTypesController::class, 'editapps'])->name('app-document.edit');
        Route::any('app/document/{id}/update', [JournalTypesController::class, 'updateapp'])->name('app-document.update');

        // Protocol Doc type
        Route::resource('protocol-types', ProtocolDocTypeController::class);
        Route::post('prt-doc-sign-roles', [ProtocolDocTypeController::class, 'signRoles'])->name('prt.doc.sign.roles');

        Route::resource('doc-org', DocOrganizationsController::class);
        Route::any('doc-org-search', [DocOrganizationsController::class, 'index'])->name('doc-org.search');
        Route::resource('protocol', DocOrganizationsController::class);
        Route::any('protocol-search', [DocOrganizationsController::class, 'index'])->name('protocol.search');

        Route::any('document-templates', [DocumentTemplatesController::class, 'index'])->name('document.templates');
        Route::any('document-templates/create', [DocumentTemplatesController::class, 'create'])->name('document.templates.create');
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
        Route::get('search-hr-emp/{doc_hash}', [ProtocolsController::class, 'searchHrEmp']);

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

        Route::any('document-create', [OnlyOfficeController::class, 'create'])->name('document-only-office.create');
        Route::get('/doc/view', [OnlyOfficeController::class, 'docView']);
        Route::get('download-pdf/{onlyDocId}', [OnlyOfficeController::class, 'convertToPDF'])->name('download-only-office.pdf');
        Route::any('edit-doc-only/{j_id}/{hash}', [OnlyOfficeController::class, 'edit'])->name('office.doc.edit-only');

        Route::get('view-inbox-doc/{id}/{hash}', [JournalController::class, 'viewInbox'])->name('office.view-inbox-doc');
        Route::get('view-sent-doc/{id}/{hash}', [JournalController::class, 'viewSent'])->name('office.view-sent-doc');
        Route::get('view-work-doc/{id}/{hash}', [JournalController::class, 'viewWork'])->name('office.view-work-doc');

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
        Route::get('search-users', [MessageController::class, 'searchUsers'])->name('messages.search-users');
        Route::get('delete/{id}', [MessageController::class, 'delete'])->name('messages.delete');
        Route::get('users-work-history/active', [MessageController::class, 'getActiveUsers'])->name('users.work-history.active');

        Route::prefix('file')->group(function () {
            Route::get('pre-view/{id}', [MessageController::class, 'preView'])->name('messages.file.pre-view');
            Route::get('download/{id}', [MessageController::class, 'fileDownload'])->name('messages.file.download');
            Route::get('del/{id}', [MessageController::class, 'fileDelete'])->name('messages.file.delete');
        });
    });
});

Route::get('p/{doc_code}', [QrCodeDocController::class, 'viewProtocolDocument']);
Route::get('d/{doc_hash}', [QrCodeDocController::class, 'viewWorkDocument']);
Route::post('/confirm-key', [QrCodeDocController::class, 'confirmKey'])->name('confirm-key');
Route::post('/create-key', [QrCodeDocController::class, 'createQrKeyCode'])->name('create-key');

Route::get('/send-telegram-notification/{user_id}', [TelegramWebhookController::class, 'sendTelegramNotification']);
