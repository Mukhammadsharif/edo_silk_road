<?php

namespace App\Models;

use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\StatusList;
use App\Models\Admin\Subscriptions;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserRole;
use App\Models\Admin\UserWorkHistory;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\TranslationLoader\LanguageLine;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $fillable = [
        'username',
        'email',
        'user_hash',
        'password',
        'org_unit_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    public function userPersonal(): BelongsTo
    {
        return $this->belongsTo(UserPersonalInfo::class, 'id', 'user_id');
    }

    public function currentWork(): BelongsTo
    {
        return $this->belongsTo(UserWorkHistory::class, 'id', 'user_id')
            ->orderBy('id', 'DESC');
    }

    public function userWorkIDs()
    {
        // return array — work_history IDs только в ТЕКУЩЕЙ выбранной организации.
        // См. комментарий в Controller.php::authActiveUserIds() о причине.
        $user = Auth::user();
        if (!$user) return [];

        return UserWorkHistory::where('user_id', $user->id)
            ->where('org_id', $user->org_unit_id)
            ->where('status', 1)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Список организаций, к которым у пользователя есть доступ
     * (одобренный work_history со status=1).
     *
     * Используется для дропдауна в сайдбаре.
     *
     * @return \Illuminate\Support\Collection of OrganizationUnits
     */
    public function accessibleOrganizations()
    {
        $orgIds = UserWorkHistory::where('user_id', $this->id)
            ->where('status', 1)
            ->pluck('org_id')
            ->unique()
            ->values()
            ->toArray();

        if (empty($orgIds)) return collect();

        return OrganizationUnits::whereIn('id', $orgIds)
            ->where('status', 1)
            ->orderBy('name_uz')
            ->get();
    }

    public function userRoles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserRole::class,'model_id','id');
    }

    /*    public function getSidebarMenu()
        {
            //
            $activeRoles = Role::where('status', 1)->pluck('id')->toArray();
            $roleID = [];
            $userRolesToArray = UserRole::where('model_id', Auth::id())->whereIn('role_id', $activeRoles)
                ->pluck('role_id')->toArray();

            if (count($userRolesToArray)) {
                $roleID = $userRolesToArray;
            }

            return RoleMenu::where('parent_id', 0)->where('status', 1)->whereIn('role_id', $roleID)
                ->orderBy('order_number')->get();
        }*/

    public function sidebarMenus()
    {
        $ttl = config('cache.sidebar_menu_ttl', 3600); // default 1 kun

        return Cache::remember("user_{$this->id}_menus", $ttl, function () {
            return $this->userRoles()
                ->with(['menus' => function ($q) {
                    $q->where('status', 1)
                        ->where('parent_id', 0) // faqat parent menyular
                        ->orderBy('order_number')
                        ->with(['child' => function ($child) {
                            $child->where('status', 1)->orderBy('order_number');
                        }]);
                }])
                ->get()
                ->pluck('menus')
                ->flatten()
                ->unique('id')
                ->values();
        });
    }

    public static function isActive($key) {

        $lang_name = 'Status';

        $status_list = StatusList::where('type', 'status')->where('code', $key)->first();

        if ($status_list) {

            $language = LanguageLine::find($status_list->lang_id);

            $lang_name = $status_list->title;

            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }

        }

        return $lang_name;
    }

    public static function fromPositionDate($dateStr): DateTime|false|string
    {
        $date = DateTime::createFromFormat('Y-m-d', $dateStr);

        $months = [
            'uz' => [
                1 => 'yanvar', 2 => 'fevral', 3 => 'mart', 4 => 'aprel',
                5 => 'may', 6 => 'iyun', 7 => 'iyul', 8 => 'avgust',
                9 => 'sentyabr', 10 => 'oktyabr', 11 => 'noyabr', 12 => 'dekabr'
            ],
            'ru' => [
                1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
                5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
                9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
            ],
            'en' => [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ]
        ];

        $day = $date->format('d');
        $month = (int)$date->format('m');
        $year = $date->format('Y');

        $locale = session()->get('locale');

        switch ($locale) {
            case 'uz':
                return "$year yil $day " . $months['uz'][$month];
            case 'ru':
                return "$day " . $months['ru'][$month] . " $year года";
            case 'en':
                return $months['en'][$month] . " $day, $year";
            default:
                return $dateStr;
        }

    }

    public function organization()
    {
        return $this->belongsTo(OrganizationUnits::class, 'org_unit_id');
    }

    public function currentSubscription()
    {
        return $this->hasOneThrough(
            Subscriptions::class,
            OrganizationUnits::class,
            'id',          // FK в organization_units
            'org_unit_id', // FK в subscriptions
            'org_unit_id', // local key в текущей модели
            'id'           // local key в organization_units
        )
            ->where('subscriptions.status', 1)
            ->where('subscriptions.ends_at', '>=', now())
            ->whereRaw('
            (SELECT COUNT(*)
             FROM document_journals
             WHERE document_journals.org_unit_id = subscriptions.org_unit_id
             AND document_journals.created_at BETWEEN subscriptions.starts_at AND subscriptions.ends_at
            ) < (SELECT max_documents
                 FROM tariffs
                 WHERE tariffs.id = subscriptions.tariff_id)
        ');
    }

    public function image()
    {
        if (file_exists(public_path().'/profile_img/'.'img_'.$this->id.'.png')) {
            return url('/profile_img/'.'img_'.$this->id.'.png');
        } else {
            return url('/images/profile_image1.png');
        }
    }

}
