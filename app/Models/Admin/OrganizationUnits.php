<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
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
}
