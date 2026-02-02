<?php

namespace App\Models\Admin;

use App\Models\Journal\JournalTypes;
use App\Models\Protocol\ProtocolDocType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\TranslationLoader\LanguageLine;

class OrganizationUnits extends Model
{
    //
    protected $fillable = [
        'code',
        'name_uz',
        'name_kr',
        'name_ru',
        'name_en',
        'tax_number',
        'owner_user_id',
        'parent_id',
        'level',
        'status',
        'meta_data'
    ];

    public function parent()
    {
        return $this->belongsTo(OrganizationUnits::class, 'parent_id', 'id');
    }

    public static function parentLang($parent_id) {

        $lang_name = OrganizationUnits::find($parent_id);

        $lang = session()->get('locale');

        $name = '-';

        if ($lang_name) {
            $name = $lang_name->name_uz;
            if ($lang == 'ru') {
                $name = $lang_name->name_ru;
            } elseif ($lang == 'en') {
                $name = $lang_name->name_en;
            } elseif ($lang == 'kr') {
                $name = $lang_name->name_kr;
            }
        }

        return $name;
    }

    public function childs() {

        return $this->hasMany(OrganizationUnits::class,'parent_id','id')
            ->where('status', 1) ;
    }

    public static function lang($id) {

        $lang_name = OrganizationUnits::find($id);

        $lang = session()->get('locale');

        $name = 'Lang ID';

        if ($lang_name) {
            $name = $lang_name->name_uz;
            if ($lang == 'ru') {
                $name = $lang_name->name_ru;
            } elseif ($lang == 'en') {
                $name = $lang_name->name_en;
            } elseif ($lang == 'kr') {
                $name = $lang_name->name_kr;
            }
        }

        return $name;
    }

    public static function isActive($key) {

        $lang_name = 'Status';

        $status_list = StatusList::where('type', 'status')->where('code', $key)->first();

        if ($status_list) {

            $language = LanguageLine::find($status_list->lang_id);

            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }

        }

        return $lang_name;
    }

    public function allParents()
    {
        $parents = collect();
        $parent = $this->parent;

        while ($parent) {
            $parents->push($parent);
            $parent = $parent->parent;
        }

        return $parents;
    }

    public function branchParents()
    {
        $parents = collect();
        $parent = $this->parent;

        while ($parent) {
            $parents->push($parent);
            $parent = $parent->parent;
        }

        return $parents;
    }

    public function children()
    {
        return $this->hasMany(OrganizationUnits::class, 'parent_id', 'dep_id');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function allChildrenRecursive()
    {
        $children = collect();

        foreach ($this->children as $child) {
            $children->push($child);
            $children = $children->merge($child->allChildrenRecursive());
        }

        return $children;
    }

    public function journalTypes()
    {
        return $this->hasMany(JournalTypes::class, 'org_id', 'id')->where('status', 1);
    }

    public function protocolTypes()
    {
        return $this->hasMany(ProtocolDocType::class, 'org_id', 'id')->where('status', 1);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscriptions::class, 'org_unit_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscriptions::class, 'org_unit_id');
    }

    public function pendingSubscription()
    {
        return $this->hasOne(Subscriptions::class, 'org_unit_id')->where('status', 2);
    }

    public function currentSubscription()
    {
        return $this->hasOne(Subscriptions::class, 'org_unit_id')
            ->with('tariff')
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

    public function canAddUser()
    {
        $subscription = $this->currentSubscription;

        if (!$subscription) {
            return false;
        }

        $currentUsersCount = User::where('org_unit_id', $this->id)->count();

        return $currentUsersCount < $subscription->tariff->max_users;
    }

    public function canCreateDocument()
    {
        $subscription = $this->currentSubscription;

        if (!$subscription) {
            return false;
        }

        $documentsCount = DB::table('document_journals')
            ->where('org_unit_id', $this->id)
            ->whereBetween('created_at', [$subscription->starts_at, $subscription->ends_at])
            ->count();

        return $documentsCount < $subscription->tariff->max_documents;
    }

    public function getSubscriptionLimits()
    {
        $subscription = $this->currentSubscription;

        if (!$subscription) {
            return null;
        }

        $currentUsers = User::where('org_unit_id', $this->id)->count();

        $currentDocuments = DB::table('document_journals')
            ->where('org_unit_id', $this->id)
            ->whereBetween('created_at', [$subscription->starts_at, $subscription->ends_at])
            ->count();

        return [
            'users' => [
                'current' => $currentUsers,
                'max' => $subscription->tariff->max_users,
                'available' => $subscription->tariff->max_users - $currentUsers,
                'percentage' => round(($currentUsers / $subscription->tariff->max_users) * 100, 2)
            ],
            'documents' => [
                'current' => $currentDocuments,
                'max' => $subscription->tariff->max_documents,
                'available' => $subscription->tariff->max_documents - $currentDocuments,
                'percentage' => round(($currentDocuments / $subscription->tariff->max_documents) * 100, 2)
            ]
        ];
    }
}
