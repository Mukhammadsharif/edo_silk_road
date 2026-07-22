<?php

namespace App\Models\Admin;

use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\Journal\JournalTypeControls;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocType;
use App\Models\Protocol\ProtocolDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\TranslationLoader\LanguageLine;

class RoleMenu extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'menu_id',
        'menu_type',
        'parent_id',
        'order_number',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo(RoleMenu::class, 'parent_id');
    }

    public function child() {

        return $this->hasMany(RoleMenu::class,'parent_id','id')
            ->where('status', 1)->orderBy('order_number');
    }

    public function roleName()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function menuList()
    {
        return $this->belongsTo(MenuList::class, 'menu_id');
    }

    public static function getMenuName($menu_id)
    {

        $lang_name = '-';
        $model = MenuList::find($menu_id);
        if ($model) {
            $lang_name = $model->title;

            $language = LanguageLine::find($model->lang_id);

            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }
        }

        return $lang_name;
    }

    public static function getKolOrg($menu_id)
    {
        $protocol = [];
        $model = MenuList::find($menu_id);
        if ($model) {
            $protocol = ProtocolDocType::where('doc_code', 'KOL_ORG')->where('status', 1)->first();
        }

        return $protocol;

    }

    public static function getPrtDocs($menu_id)
    {
        $protocol = [];
        $model = MenuList::find($menu_id);
        if ($model) {
            $protocol = ProtocolDocType::where('doc_code', 'PRT_DOCS')->where('status', 1)->first();
        }

        return $protocol;

    }

    public static function menuCount($id)
    {
        $roleMenu = RoleMenu::find($id);

        $count = 0;

        $workIds = UserWorkHistory::where('user_id', Auth::id())->pluck('id')->toArray();;

        $controls = JournalTypeControls::where('status', 1)->pluck('journal_type_id')->toArray();

        $isSuperUser = in_array('Super_Admin', self::authActiveUserRoleNames());

        if ($roleMenu && !str_contains($roleMenu->menuList->code, 'PRT_')) {
            $code = $roleMenu->menuList->code??'*';
            $count = match ($code) {
                'OF_W_DOC' => DocumentJournals::whereIn('status', [1, 3])
                    ->whereIn('created_user', $workIds)
                    ->whereHas('journal', function ($query) {
                        $query->whereNotIn('doc_code', ['BASE_INCOMING', 'BASE_OUTGOING']);
                    })->count(),
                'OF_IN_DOC' => DocumentJournals::whereIn('status', [1, 3])
                    ->whereIn('created_user', $workIds)
                    ->whereHas('journal', function ($query) {
                        $query->where('doc_code', 'BASE_INCOMING');
                    })->count(),
                'OF_OUT_DOC' => DocumentJournals::whereIn('status', [1, 3])
                    ->whereIn('created_user', $workIds)
                    ->whereHas('journal', function ($query) {
                        $query->where('doc_code', 'BASE_OUTGOING');
                    })->count(),
                'ALL_DOCS' => $isSuperUser ?
                    DocumentJournals::whereNotIn('status', [1, 4])->count() :
                    DocumentJournals::whereNotIn('status', [1, 4])->where('org_unit_id', self::authActiveUser()->user->org_unit_id)->count(),
                'ALL_PRT_DOCS' => $isSuperUser ? ProtocolDocument::whereNotIn('status', [1])->where('status', '!=', 6)->count()
                                : ProtocolDocument::whereNotIn('status', [1])->where('org_id', self::authActiveUser()->user->org_unit_id)->where('status', '!=', 6)->count(),
                'DOC_EXEC' => DocumentExecutionSteps::whereIn('to_user', $workIds)
                    ->whereHas('role', function ($query) { $query->where('doc_code', 'EXEC'); })
                    ->whereIn('status', [2, 4])
                    ->count(),
                'DOC_SIGN' => DocumentExecutionSteps::whereIn('to_user', $workIds)
                    ->whereHas('role', function ($query) { $query->where('doc_code', 'SIGN'); })
                    ->where('status', 2)
                    ->count(),
                'DOC_APPROVE' => DocumentExecutionSteps::whereIn('to_user', $workIds)
                    ->whereHas('role', function ($query) { $query->where('doc_code', 'AGREE'); })
                    ->where('status', 2)
                    ->count(),
                'DOC_CONF' => DocumentExecutionSteps::whereIn('to_user', $workIds)
                    ->whereHas('role', function ($query) { $query->where('doc_code', 'RESOL'); })
                    ->whereIn('status', [2, 6])
                    ->count(),
                'PRT_HR_S' => ProtocolDocument::whereHas('docType', function ($query){
                    $query->where('doc_code', 'PRT_HR');
                })->whereIn('status', [1,2,4,5])->count(),
                'PRT_HR' => ProtocolDocMember::whereHas('document', function ($query) {
                    $query->whereHas('docType', function ($query1) {
                            $query1->where('doc_code', 'PRT_HR');
                        });
                    })->where('role_id', '!=', 4)
                    ->whereIn('user_id', $workIds)
                    ->where('status', 2)->count(),
                'PRT_CR' => ProtocolDocMember::whereHas('document', function ($query) {
                    $query->whereHas('docType', function ($query1) {
                            $query1->where('doc_code', 'PRT_CR');
                        });
                    })->where('role_id', '!=', 4)
                    ->whereIn('user_id', $workIds)
                    ->where('status', 2)->count(),
                'PRT_AL' => ProtocolDocMember::whereHas('document', function ($query) {
                    $query->whereHas('docType', function ($query1) {
                            $query1->where('doc_code', 'PRT_AL');
                        });
                    })->where('role_id', '!=', 4)
                    ->whereIn('user_id', $workIds)
                    ->where('status', 2)->count(),
                'PRT_INS' => ProtocolDocMember::whereHas('document', function ($query) {
                    $query->whereHas('docType', function ($query1) {
                            $query1->where('doc_code', 'PRT_INS');
                        });
                    })->where('role_id', '!=', 4)->whereIn('user_id', $workIds)
                    ->where('status', 2)->count(),
                'PRT_CN' => ProtocolDocMember::whereHas('document', function ($query) {
                    $query->whereHas('docType', function ($query1) {
                            $query1->where('doc_code', 'PRT_CN');
                        });
                    })->where('role_id', '!=', 4)->whereIn('user_id', $workIds)
                    ->where('status', 2)->count(),
                'PRT_HR_EMP' => ProtocolDocMember::where('role_id', 4)->whereIn('user_id', $workIds)
                    ->where('status', 2)->count(),
                'DOC_CONTROL' => DocumentJournals::where('org_unit_id', self::authActiveUser()->user->org_unit_id)->whereIn('status', [7, 11])->whereIn('j_type', $controls)->count(),
                'MESS' => DB::table('messages')
                    ->leftJoin('message_users', 'messages.id', '=', 'message_users.message_id')
                    ->leftJoin('message_forwards', 'messages.id', '=', 'message_forwards.message_id')
                    ->where(function ($query) use ($workIds) {
                        $query->where(function ($subQuery) use ($workIds) {
                            $subQuery->whereIn('message_users.to_w_user_id', $workIds)
                                ->whereNotIn('message_users.status', [2, 3]); // Exclude archived/deleted
                        })
                            ->orWhere(function ($subQuery) use ($workIds) {
                                $subQuery->whereIn('message_forwards.to_w_user_id', $workIds)
                                    ->whereNotIn('message_forwards.status', [2, 3]); // Exclude archived/deleted
                            });
                    })
                    ->count(DB::raw('DISTINCT messages.id')),
                default => 0,
            };
        } elseif (str_contains($roleMenu->menuList->code, 'PRT_')) {
            $count = ProtocolDocMember::whereHas('document', function ($query) use ($roleMenu) {
                $query->whereHas('docType', function ($query1) use ($roleMenu) {
                    $query1->where('doc_code', $roleMenu->menuList->code);
                });
            })->whereIn('user_id', $workIds)
                ->where('status', 2)->count();
        }

        return $count;
    }

    public static function lang($id) {

        $lang_name = '-';
        $model = RoleMenu::find($id);
        if ($model) {
            $lang_name = $model->title;

            $language = LanguageLine::find($model->lang_id);

            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }
        }

        return $lang_name;
    }

    public static function authActiveUser()
    {
        // return object — UserWorkHistory для ТЕКУЩЕЙ выбранной организации
        $user = Auth::user();
        if (!$user) return null;

        return UserWorkHistory::where('user_id', $user->id)
            ->where('org_id', $user->org_unit_id)
            ->where('status', 1)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public static function authActiveUserRoles()
    {
        // return object
        $role_ids = UserRole::where('model_id', self::authActiveUser()->user_id??0)->pluck('role_id')->toArray();

        return Role::whereIn('id', $role_ids)->get();
    }

    public static function authActiveUserRoleNames(): array
    {
        // return array
        $auth_roles = array();

        foreach (self::authActiveUserRoles() as $key => $item) {
            $auth_roles[$key] = $item->name;
        }

        return $auth_roles;
    }

}
