<?php

namespace App\Http\Controllers;

use App\Models\Admin\UserPosition;
use App\Models\Admin\UserWorkHistory;
use App\Models\Messages\MessageFiles;
use App\Models\Messages\MessageForwards;
use App\Models\Messages\Messages;
use App\Models\Messages\MessageUsers;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageController extends Controller
{
    //

    public function index()
    {
        $users = UserWorkHistory::where('status', 1)->orderBy('id', 'desc')->get();

        $authIds = $this->authActiveUserIds();

        // Inbox messages (active, to_w_user_id in $authIds)
        $inboxMessagesCount = DB::table('messages')
            ->leftJoin('message_users', 'messages.id', '=', 'message_users.message_id')
            ->leftJoin('message_forwards', 'messages.id', '=', 'message_forwards.message_id')
            ->where(function ($query) use ($authIds) {
                $query->where(function ($subQuery) use ($authIds) {
                    $subQuery->whereIn('message_users.to_w_user_id', $authIds)
                        ->whereNotIn('message_users.status', [2, 3]); // Exclude archived/deleted
                })
                    ->orWhere(function ($subQuery) use ($authIds) {
                        $subQuery->whereIn('message_forwards.to_w_user_id', $authIds)
                            ->whereNotIn('message_forwards.status', [2, 3]); // Exclude archived/deleted
                    });
            })
            ->count(DB::raw('DISTINCT messages.id'));

        // Outbox messages (active, from_w_user_id or from_w_user_id in $authIds)
        $outMessagesCount = DB::table('messages')
            ->leftJoin('message_users', 'messages.id', '=', 'message_users.message_id')
            ->leftJoin('message_forwards', 'messages.id', '=', 'message_forwards.message_id')
            ->where(function ($query) use ($authIds) {
                $query->where(function ($subQuery) use ($authIds) {
                    $subQuery->whereIn('messages.from_w_user_id', $authIds)
                        ->whereNotIn('message_users.status', [3]); // Exclude archived/deleted
                })
                    ->orWhere(function ($subQuery) use ($authIds) {
                        $subQuery->whereIn('message_forwards.from_w_user_id', $authIds)
                            ->whereNotIn('message_forwards.status', [3]); // Exclude archived/deleted
                    });
            })
            ->count(DB::raw('DISTINCT messages.id'));

        $archivedCount = DB::table('messages')
            ->leftJoin('message_users', 'messages.id', '=', 'message_users.message_id')
            ->leftJoin('message_forwards', 'messages.id', '=', 'message_forwards.message_id')
            ->where(function ($query) use ($authIds) {
                $query->where(function ($sub) use ($authIds) {
                    $sub->whereIn('message_users.to_w_user_id', $authIds)
                        ->where('message_users.status', 2);
                })
                    ->orWhere(function ($sub) use ($authIds) {
                        $sub->whereIn('messages.from_w_user_id', $authIds)
                            ->where('message_users.status', 2);
                    })
                    ->orWhere(function ($sub) use ($authIds) {
                        $sub->whereIn('message_forwards.to_w_user_id', $authIds)
                            ->where('message_forwards.status', 2);
                    })
                    ->orWhere(function ($sub) use ($authIds) {
                        $sub->whereIn('message_forwards.from_w_user_id', $authIds)
                            ->where('message_forwards.status', 2);
                    });
            })
            ->count(DB::raw('DISTINCT messages.id'));

// Deleted messages (status = 3, inbox or outbox)
        $deletedCount = DB::table('messages')
            ->leftJoin('message_users', 'messages.id', '=', 'message_users.message_id')
            ->leftJoin('message_forwards', 'messages.id', '=', 'message_forwards.message_id')
            ->where(function ($query) use ($authIds) {
                $query->where(function ($sub) use ($authIds) {
                    $sub->whereIn('message_users.to_w_user_id', $authIds)
                        ->where('message_users.status', 3);
                })
                    ->orWhere(function ($sub) use ($authIds) {
                        $sub->whereIn('messages.from_w_user_id', $authIds)
                            ->where('message_users.status', 3);
                    })
                    ->orWhere(function ($sub) use ($authIds) {
                        $sub->whereIn('message_forwards.to_w_user_id', $authIds)
                            ->where('message_forwards.status', 3);
                    })
                    ->orWhere(function ($sub) use ($authIds) {
                        $sub->whereIn('message_forwards.from_w_user_id', $authIds)
                            ->where('message_forwards.status', 3);
                    });
            })
            ->count(DB::raw('DISTINCT messages.id'));

        return view('messages.index', compact('users', 'inboxMessagesCount', 'outMessagesCount',
            'archivedCount', 'deletedCount'));
    }

    public function getActiveUsers(Request $request): JsonResponse
    {
        $users = UserWorkHistory::where('status', 1)
            ->where('id', '!=', $this->authActiveUser()->id)
            ->orderBy('id', 'desc')
            ->with([
                'personal' => function ($query) {
                    $query->select('user_id', 'f_name', 'm_name', 'l_name');
                },
                'position' => function ($query) {
                    $query->select('id', 'name_uz', 'name_ru', 'name_en', 'name_kr');
                }])
            ->get(['user_id', 'dep_id', 'post_id', 'status', 'id'])
            ->map(function ($workHistory) {
                return [
                    'id' => $workHistory->id,
                    'personal' => $workHistory->personal ? [
                        'f_name' => $workHistory->personal->f_name,
                        'm_name' => $workHistory->personal->m_name,
                        'l_name' => $workHistory->personal->l_name,
                    ] : null,
                    'position' => $workHistory->position ? UserPosition::lang($workHistory->position->id) : null,
                    'image' => url('/images/profile_image1.png'),
                ];
            });

        return response()->json(['users' => $users]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'selected_users.*' => 'integer|min:1',
        ],[
            'selected_users.*' =>  __('validate.choose_user'),
        ]);

        $selectedUsers = array_filter($request->input('selected_users'), fn($id) => is_numeric($id) && $id > 0);

        $auth = $this->authActiveUser();

        $message = new Messages();
        $message->from_w_user_id = $auth->id;
        $message->text = $request->message_text??'';
        $message->subject = $request->subject??'';
        $message->hash_code = $this->uniqueHash(10);
        $message->save();

        $this->extracted($request, $message);

        if ($selectedUsers){
            foreach ($selectedUsers as $user) {
                $messageUser = new MessageUsers();
                $messageUser->message_id = $message->id;
                $messageUser->to_w_user_id = $user;
                $messageUser->save();
            }
        }

        return redirect()->route('messages.index')->with('success', __('field.message_sent_successfully'));
    }

    public function list(Request $request, $type)
    {
        $s_text = $request->input('s_text');
        $date_from = $request->input('date_from');
        $date_till = $request->input('date_till');
        $created_user = $request->input('created_user');

        $userId = $this->authActiveUser()->id;
        $query = DB::table('messages')
            ->leftJoin('message_users', 'messages.id', '=', 'message_users.message_id')
            ->leftJoin('message_forwards', 'messages.id', '=', 'message_forwards.message_id')
            ->leftJoin('user_work_histories as sender', function ($join) {
                $join->on('sender.id', '=', DB::raw('COALESCE(message_forwards.from_w_user_id, messages.from_w_user_id)'));
            })
            ->select(
                'messages.id',
                'messages.subject',
                'messages.text',
                'sender.id as sender_name',
                'messages.created_at',
                'messages.hash_code',
                DB::raw('COALESCE(message_users.read_date, message_forwards.read_date) as read_date')
            );

        switch ($type) {
            case 'inbox':
                $query->where(function ($q) use ($userId) {
                    $q->where(function ($sub) use ($userId) {
                        $sub->where('message_users.to_w_user_id', $userId)
                            ->whereNotIn('message_users.status', [2, 3]);
                    })
                        ->orWhere(function ($sub) use ($userId) {
                            $sub->where('message_forwards.to_w_user_id', $userId)
                                ->whereNotIn('message_forwards.status', [2, 3]);
                        });
                });
                break;

            case 'archive':
                $query->where(function ($q) use ($userId) {
                    $q->where(function ($sub) use ($userId) {
                        $sub->where('message_users.to_w_user_id', $userId)
                            ->where('message_users.status', 2);
                    })
                        ->orWhere(function ($sub) use ($userId) {
                            $sub->where('messages.from_w_user_id', $userId)
                                ->where('message_users.status', 2);
                        })
                        ->orWhere(function ($sub) use ($userId) {
                            $sub->where('message_forwards.to_w_user_id', $userId)
                                ->where('message_forwards.status', 2);
                        })
                        ->orWhere(function ($sub) use ($userId) {
                            $sub->where('message_forwards.from_w_user_id', $userId)
                                ->where('message_forwards.status', 2);
                        });
                });
                break;

            case 'outbox':
                $query->where(function ($q) use ($userId) {
                    $q->where(function ($sub) use ($userId) {
                        $sub->where('messages.from_w_user_id', $userId)
                            ->whereNotIn('message_users.status', [3]);
                    })
                        ->orWhere(function ($sub) use ($userId) {
                            $sub->where('message_forwards.from_w_user_id', $userId)
                                ->whereNotIn('message_forwards.status', [3]);
                        });
                });
                break;

            case 'deleted':
                $query->where(function ($q) use ($userId) {
                    $q->where(function ($sub) use ($userId) {
                        $sub->where('message_users.to_w_user_id', $userId)
                            ->where('message_users.status', 3);
                    })
                        ->orWhere(function ($sub) use ($userId) {
                            $sub->where('messages.from_w_user_id', $userId)
                                ->where('message_users.status', 3);
                        })
                        ->orWhere(function ($sub) use ($userId) {
                            $sub->where('message_forwards.to_w_user_id', $userId)
                                ->where('message_forwards.status', 3);
                        })
                        ->orWhere(function ($sub) use ($userId) {
                            $sub->where('message_forwards.from_w_user_id', $userId)
                                ->where('message_forwards.status', 3);
                        });
                });
                break;

            default:
                abort(404, 'Invalid message type');
        }

        if ($s_text) {
            $query->where('messages.subject', 'ilike', '%' . $s_text . '%');
        }
        if ($date_from) {
            $query->whereDate('messages.created_at', '>=', $date_from);
        }
        if ($date_till) {
            $query->whereDate('messages.created_at', '<=', $date_till);
        }
        if ($created_user) {
            $query->where(function ($q) use ($created_user) {
                $q->where('messages.from_w_user_id', $created_user)
                    ->orWhere('message_forwards.from_w_user_id', $created_user);
            });
        }

        $models = $query->distinct('messages.id')
            ->orderBy('messages.id', 'desc') // Match DISTINCT ON
            ->orderBy('messages.created_at', 'desc')
            ->paginate(25);

        $models->appends([
            's_text' => $s_text,
            'date_from' => $date_from,
            'date_till' => $date_till,
            'created_user' => $created_user,
        ]);

        $users = UserWorkHistory::where('status', 1)->orderBy('id', 'desc')->get();

        return view('messages.list', compact('models', 'type', 'users', 's_text', 'date_from', 'date_till', 'created_user'));
    }

    public function view($id, $hash)
    {
        $userId = $this->authActiveUser()->id;
        $authIds = $this->authActiveUserIds();

        // Fetch the message
        $message = Messages::with(['files', 'sender.personal'])
            ->leftJoin('message_users', 'messages.id', '=', 'message_users.message_id')
            ->leftJoin('message_forwards', 'messages.id', '=', 'message_forwards.message_id')
            ->leftJoin('user_work_histories as sender', function ($join) {
                $join->on('sender.id', '=', DB::raw('COALESCE(message_forwards.from_w_user_id, messages.from_w_user_id)'));
            })
            ->select(
                'messages.*',
                'sender.id as sender_name',
                DB::raw('COALESCE(message_users.read_date, message_forwards.read_date) as read_date')
            )
            ->where('messages.id', $id)
            ->where('messages.hash_code', $hash)
            ->firstOrFail();

        // Check if it's an inbox message and update status/read_date
        $messageUser = MessageUsers::where('message_id', $id)
            ->where('to_w_user_id', $userId)
            ->where('status', 1)
            ->first();

        $messageForward = MessageForwards::where('message_id', $id)
            ->where('to_w_user_id', $userId)
            ->where('status', 1)
            ->first();

        if ($messageUser) {
            $messageUser->status = 2;
            $messageUser->read_date = Carbon::now();
            $messageUser->save();
        } elseif ($messageForward) {
            $messageForward->status = 2;
            $messageForward->read_date = Carbon::now();
            $messageForward->save();
        }

        // Fetch recipients (message_users and message_forwards)
        $recipients = collect();

        $messageUsers = MessageUsers::where('message_id', $id)
            ->with(['user' => function ($query) {
                $query->with(['personal', 'userPost']);
            }])
            ->get();

        $messageForwards = MessageForwards::where('message_id', $id)
            ->with(['user' => function ($query) {
                $query->with(['personal', 'userPost']);
            }])
            ->get();

        $recipients = $recipients->merge($messageUsers->map(function ($item) {
            if (!$item->user->personal) {
                Log::warning("User ID {$item->user->id} has no personal record in MessageUsers for message_id {$item->message_id}");
            }
            return (object) [
                'user' => $item->user,
                'status' => $item->status,
                'read_date' => $item->read_date,
            ];
        }));

        $recipients = $recipients->merge($messageForwards->map(function ($item) {
            if (!$item->user->personal) {
                Log::warning("User ID {$item->user->id} has no personal record in MessageForwards for message_id {$item->message_id}");
            }
            return (object) [
                'user' => $item->user,
                'status' => $item->status,
                'read_date' => $item->read_date,
            ];
        }));

        return view('messages.view', compact('message', 'recipients', 'authIds'));
    }

    public function forward(Request $request, $id)
    {
        $userId = $this->authActiveUser()->id;
        $message = Messages::findOrFail($id);
        $userIds = $request->input('selected_users', []);

        // Filter out empty or invalid IDs
        $userIds = array_filter($userIds, function($id) {
            return is_numeric($id) && $id > 0;
        });

        if (empty($userIds)) {
            return response()->json(['res' => 0, 'message' => __('field.select_at_least_one_user')]);
        }

        foreach ($userIds as $toUserId) {
            if ($toUserId != $userId) {
                MessageForwards::create([
                    'message_id' => $message->id,
                    'from_w_user_id' => $userId,
                    'to_w_user_id' => $toUserId,
                    'status' => 1,
                    'read_date' => null,
                ]);
            }
        }

        return response()->json(['res' => 1, 'message' => __('message_forwarded')]);
    }

    public function reply(Request $request, $id)
    {
        $userId = $this->authActiveUser()->id;
        $message = Messages::with('sender')->findOrFail($id);

        $request->validate([
            'subject' => 'required|string|max:255',
            'text' => 'required|string',
        ]);

        $newMessage = Messages::create([
            'from_w_user_id' => $userId,
            'subject' => $request->input('subject'),
            'text' => $request->input('text'),
            'reply_message_id' => $message->id,
            'hash_code' => Str::random(32),
        ]);

        MessageUsers::create([
            'message_id' => $newMessage->id,
            'to_w_user_id' => $message->from_w_user_id,
            'status' => 1,
            'read_date' => null,
        ]);

        $this->extracted($request, $newMessage);

        return response()->json(['res' => 1, 'message' => __('message_sent')]);
    }

    public function delete($id)
    {
        $userId = $this->authActiveUser()->id;
        $authIds = $this->authActiveUserIds();

        // Find message in message_users or message_forwards
        $messageUser = MessageUsers::where('message_id', $id)
            ->where('to_w_user_id', $userId)
            ->first();

        $messageForward = MessageForwards::where('message_id', $id)
            ->where(function ($q) use ($userId) {
                $q->where('to_w_user_id', $userId)
                    ->orWhere('from_w_user_id', $userId);
            })
            ->first();

        $message = Messages::whereIn('from_w_user_id', $authIds)->where('id', $id)->first();

        if ($messageUser) {
            $messageUser->status = 3; // Mark as deleted
            $messageUser->save();
        } elseif ($messageForward) {
            $messageForward->status = 3; // Mark as deleted
            $messageForward->save();
        } elseif ($message) {
            MessageUsers::where('message_id', $id)->update(['status' => 3]);
            MessageForwards::where('message_id', $id)->update(['status' => 3]);
        }  else {
            return response()->json(['res' => 0, 'message' => __('message_not_found')]);
        }

        return response()->json(['res' => 1, 'message' => __('message_deleted')]);
    }


    public function fileDownload($id): StreamedResponse|RedirectResponse
    {
        //
        $model = MessageFiles::find($id);

        if ($model) {
            $path = '/'.$model->file_path.$model->file_hash;

            if (Storage::disk('ftp_13')->exists($path)){

                return Storage::disk('ftp_13')->download($path, $model->file_name);
            }

        }

        return back()->with('error', __('document_not_found'));
    }

    public function preView($id): \Illuminate\Http\Response|StreamedResponse|JsonResponse
    {
        //
        $model = MessageFiles::find($id);

        if ($model) {
            $path = '/'.$model->file_path.$model->file_hash;

            if (Storage::disk('ftp_13')->exists($path)){

                $res = Storage::disk('ftp_13')->get($path);

                if (strtoupper($model->file_ext) == 'PDF'){

                    return Response::make($res, 200, [
                        'Content-Type'        => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="'.$model->file_name.'"'
                    ]);

                } elseif (strtoupper($model->file_ext) == 'PNG' || strtoupper($model->file_ext) == 'JPG' ||
                    strtoupper($model->file_ext) == 'JPEG'){

                    return Response::make($res, 200, [
                        'Content-Type'        => 'image/png',
                        'Content-Disposition' => 'inline; filename="'.$model->file_name.'"'
                    ]);
                } else {

                    return Storage::disk('ftp_13')->download($path, $model->file_name);
                }
            }

        }

        return response()->json(__('document_not_found'));

    }

    public function fileDelete($id): JsonResponse
    {
        $file = MessageFiles::find($id);
        $res = 1;
        $message = __('unk');
        if ($file) {
            $path = '/'.$file->file_path.$file->file_hash;

            if (Storage::disk('ftp_13')->exists($path)){

                Storage::disk('ftp_13')->delete($path);
            }
            $file->delete();

            $res = 2;
            $message = __('file_succ_deleted');
        }

        return response()->json(['res' => $res, 'id' => $id, 'message' => $message]);
    }

    /**
     * @param Request $request
     * @param $newMessage
     * @return void
     */
    public function extracted(Request $request, $newMessage): void
    {
        if ($request->hasFile('doc_files')) {
            $mesFiles = $request->file('doc_files');
            foreach ($mesFiles as $file) {
                $today = Carbon::today();
                $year = $today->year;
                $month = $today->month;
                $day = $today->day;
                $path = 'messages/' . $year . '/' . $month . '/' . $day . '/';

                $message_file = new MessageFiles();
                $message_file->message_id = $newMessage->id;
                $message_file->file_name = $file->getClientOriginalName();;
                $message_file->file_hash = $this->uniqueHash(16) . '.' . $file->getClientOriginalExtension();
                $message_file->file_path = $path;
                $message_file->file_ext = $file->getClientOriginalExtension();
                $message_file->file_size = $file->getSize();
                Storage::disk('ftp_13')->put($path . $message_file->file_hash, file_get_contents($file->getRealPath()));
                $message_file->save();
            }
        }
    }
}
