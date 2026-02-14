<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class UserRole extends Model
{
    protected $table = 'model_has_roles';

    protected $fillable = [
        'role_id',
        'model_type',
        'model_id',
    ];

    public function menus(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RoleMenu::class, 'role_id', 'role_id');
    }

    public static function lang($id) {

        $role = Role::where('id', $id)->first();

        $lang_name = $role->name??'-';

        if ($role) {

            $language = LanguageLine::find($role->lang_id);

            if ($language) {
                $lang_name = __($language->group.'.'.$language->key);
            }

        }

        return $lang_name;
    }

}
