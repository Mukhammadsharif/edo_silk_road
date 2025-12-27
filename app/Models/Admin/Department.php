<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class Department extends Model
{
    protected $fillable = [
        'code',
        'org_unit_id',
        'name_uz',
        'name_kr',
        'name_ru',
        'name_en',
        'type',
        'parent_id',
        'order_number',
        'level_number',
        'status',
        'meta_data'
    ];

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id', 'id');
    }

    public static function parentLang($parent_id) {

        $lang_name = Department::find($parent_id);

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

        return $this->hasMany(Department::class,'parent_id','id')
            ->orderBy('order_number')
            ->where('status', 1) ;
    }

    public static function lang($id) {

        $lang_name = Department::find($id);

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

    public static function docLang($id, $lang)
    {

        $lang_name = Department::find($id);

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
        return $this->hasMany(Department::class, 'parent_id', 'dep_id');
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

    public function activeWorkers()
    {
        return $this->hasMany(UserWorkHistory::class, 'dep_id')->where('status', 1);
    }
}
