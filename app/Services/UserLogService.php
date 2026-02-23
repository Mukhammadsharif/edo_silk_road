<?php

namespace App\Services;

use App\Models\Admin\UserLog;
use Illuminate\Support\Facades\Auth;

class UserLogService
{
    //
    protected UserLog $userLog;
    public function __construct(UserLog $userLog)
    {
        //
        $this->userLog = $userLog;
    }

    public function createUserLog($user_id, $details): true
    {
        //
        $create = new UserLog();
        $create->action = 'create';
        $create->user_id = $user_id;
        $create->modified_by = Auth::id();
        $create->details = $details;
        $create->save();

        return true;
    }

    public function editUserLog($user_id, $details): true
    {
        //
        $create = new UserLog();
        $create->action = 'edit';
        $create->user_id = $user_id;
        $create->modified_by = Auth::id();
        $create->details = $details;
        $create->save();

        return true;
    }

    public function getList()
    {
        //
        return UserLog::get();
    }
}
