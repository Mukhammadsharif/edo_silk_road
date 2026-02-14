<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class UserPersonalNames extends Model
{
    //
    protected $fillable = [
        'f_name_kr',
        'l_name_kr',
        'm_name_kr',
        'f_name_ru',
        'l_name_ru',
        'm_name_ru',
        'f_name_en',
        'l_name_en',
        'm_name_en',
    ];
}
