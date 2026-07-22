<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPersonalInfo extends Model
{
    protected $fillable = [
        'user_id',
        'is_emp',
        'doc_pin',
        'doc_number',
        'doc_type',
        'birthday',
        'gender',
        'phone',
        'f_name',
        'l_name',
        'm_name',
        'address',
        'doc_sign_type',
        'sort',
        'lang_code',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function fullName()
    {
        return $this->l_name.' '.$this->f_name.' '.$this->m_name;
    }

    public function shortName()
    {

        $short_f_name = '';
        if (trim(''.$this->f_name.'')) {
            switch (mb_substr($this->f_name, 0,2)) {
                case 'CH':
                case 'Ch':
                case 'cH':
                case 'ch':
                case 'SH':
                case 'Sh':
                case 'sH':
                case 'sh':
                    $first_name_short = mb_substr($this->f_name, 0,2);
                    break;

                default:
                    $first_name_short = mb_substr($this->f_name, 0,1);
                    break;
            }
            $short_f_name = $first_name_short.'.';
        }

        $short_m_name = '';
        if (trim(''.$this->m_name.'')) {
            switch (mb_substr($this->m_name, 0,2)) {
                case 'CH':
                case 'Ch':
                case 'cH':
                case 'ch':
                case 'SH':
                case 'Sh':
                case 'sH':
                case 'sh':
                    $middle_name_short = mb_substr($this->m_name, 0,2);
                    break;

                default:
                    $middle_name_short = mb_substr($this->m_name, 0,1);
                    break;
            }
            $short_m_name = $middle_name_short.'.';
        }

        $short_l_name = '';
        if (trim(''.$this->l_name.'')) {
            $short_l_name = $this->l_name;
        }

        $short_name = strtoupper($short_f_name).strtoupper($short_m_name).$short_l_name;

        return $short_name;
    }

    public function shortName1()
    {

        $short_f_name = '';
        if (trim(''.$this->f_name.'')) {
            switch (mb_substr($this->f_name, 0,2)) {
                case 'CH':
                case 'Ch':
                case 'cH':
                case 'ch':
                case 'SH':
                case 'Sh':
                case 'sH':
                case 'sh':
                    $first_name_short = mb_substr($this->f_name, 0,2);
                    break;

                default:
                    $first_name_short = mb_substr($this->f_name, 0,1);
                    break;
            }
            $short_f_name = $first_name_short.'.';
        }

        $short_l_name = '';
        if (trim(''.$this->l_name.'')) {
            $short_l_name = $this->l_name;
        }

        $short_name = strtoupper($short_f_name).$short_l_name;

        return $short_name;
    }

    public function langName()
    {
        return $this->belongsTo(UserPersonalNames::class, 'user_id', 'user_id');
    }

    public function fullNameLang($docLang)
    {
       if ($docLang && $this->langName && $docLang !== 'uz') {
           $l_name = 'l_name_'.$docLang;
           $f_name = 'f_name_'.$docLang;
           $m_name = 'm_name_'.$docLang;
           return $this->langName->$l_name.' '.$this->langName->$f_name.' '.$this->langName->$m_name;
       } else {
           return $this->fullName();
       }
    }

    public function shortNameLang($docLang)
    {
        if ($docLang && $this->langName && $docLang !== 'uz') {
            $l_name_query = 'l_name_'.$docLang;
            $f_name_query = 'f_name_'.$docLang;
            $m_name_query = 'm_name_'.$docLang;
            $f_name = $this->langName->$f_name_query;
            $l_name = $this->langName->$l_name_query;
            $m_name = $this->langName->$m_name_query;
        } else {
            $f_name = $this->f_name;
            $l_name = $this->l_name;
            $m_name = $this->m_name;
        }

        $short_f_name = '';
        if (trim(''.$f_name.'')) {
            switch (mb_substr($f_name, 0,2)) {
                case 'CH':
                case 'Ch':
                case 'cH':
                case 'ch':
                case 'SH':
                case 'Sh':
                case 'sH':
                case 'sh':
                    $first_name_short = mb_substr($f_name, 0,2);
                    break;

                default:
                    $first_name_short = mb_substr($f_name, 0,1);
                    break;
            }
            $short_f_name = $first_name_short.'.';
        }

        $short_m_name = '';
        if (trim(''.$m_name.'')) {
            switch (mb_substr($m_name, 0,2)) {
                case 'CH':
                case 'Ch':
                case 'cH':
                case 'ch':
                case 'SH':
                case 'Sh':
                case 'sH':
                case 'sh':
                    $middle_name_short = mb_substr($m_name, 0,2);
                    break;

                default:
                    $middle_name_short = mb_substr($m_name, 0,1);
                    break;
            }
            $short_m_name = $middle_name_short.'.';
        }

        $short_l_name = '';
        if (trim(''.$l_name.'')) {
            $short_l_name = $l_name;
        }

        $short_name = strtoupper($short_f_name).strtoupper($short_m_name).$short_l_name;

        return $short_name;
    }
}
