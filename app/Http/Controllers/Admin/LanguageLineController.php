<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Spatie\TranslationLoader\LanguageLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LanguageLineController extends Controller
{
    //
    public function index(Request $request)
    {
        $s_text = $request->input('s_text');

        $s_field = $request->input('s_field');

        $search = LanguageLine::orderBy('id', 'DESC');

        $groups = LanguageLine::select('group', DB::raw('count(*) as total'))
            ->groupBy('group')
            ->get();

        if (trim(''.$s_text.'')) {
            $search->where('key', 'like', "%{$s_text}%")
                ->orWhere('text->en', 'like', "%{$s_text}%")
                ->orWhere('text->ru', 'like', "%{$s_text}%")
                ->orWhere('text->uz', 'like', "%{$s_text}%");
        }

        if ($s_field){
            $search->where('group', $s_field);
        }

        $models = $search->paginate(25);

        $models->appends([
            's_text' => $s_text,
            's_field' => $s_field,
        ]);

        return view('admin.language-line.index', compact('s_text', 'groups', 's_field', 'models'));
    }

    public function fetchLanguage()
    {
        $models = LanguageLine::orderBy('group')->paginate(25);

        return response()->json([
            'models'=>$models,
        ]);

    }

    public function store(Request $request)
    {
        //print_r($request->all()); die;
        $validator = Validator::make($request->all(), [
            'group_input'=> 'required|max:191',
            'key_input'=>'required|max:191',
            'text_uz_input'=>'required|max:50',
            'text_ru_input'=>'required|max:50',
            'text_en_input'=>'required|max:50',
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status'=>400,
                'errors'=>$validator->messages()
            ]);
        }
        else
        {
            LanguageLine::create([
                'group' => $request->group_input,
                'key' => $request->key_input,
                'text' => [
                    'uz' => $request->text_uz_input,
                    'ru' => $request->text_ru_input,
                    'en' => $request->text_en_input,
                ],
            ]);

            return response()->json([
                'status'=>200,
                'message'=>'Student Added Successfully.'
            ]);
        }

    }

    public function edit($id)
    {
        $model = LanguageLine::find($id);

        if($model) {
            return response()->json([
                'status'=>200,
                'model'=> $model,
            ]);
        } else {
            return response()->json([
                'status'=>404,
                'message'=>'No Student Found.'
            ]);
        }

    }

    public function update(Request $request, $id)
    {
        //print_r($request->all()); die;
        $validator = Validator::make($request->all(), [
            'group_input'=> 'required|max:191',
            'key_input'=>'required|max:191',
            'text_uz_input'=>'required|max:50',
            'text_ru_input'=>'required|max:50',
            'text_en_input'=>'required|max:50',
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status'=>400,
                'errors'=>$validator->messages()
            ]);
        }
        else
        {
            $model = LanguageLine::find($id);

            if($model) {
                /*$text = [
                    'uz' => $request->input('text_uz_input'),
                    'ru' => $request->input('text_ru_input'),
                    'en' => $request->input('text_en_input')
                ];

                $model->group = $request->input('group_input');
                $model->key = $request->input('key_input');
                $model->text = json_encode($text);
                $model->update();*/

                LanguageLine::find($id)->update([
                    'group' => $request->group_input,
                    'key' => $request->key_input,
                    'text' => [
                        'uz' => $request->text_uz_input,
                        'ru' => $request->text_ru_input,
                        'en' => $request->text_en_input,
                    ],
                ]);

                return response()->json([
                    'status'=>200,
                    'message'=>'Student Updated Successfully.'
                ]);
            }
            else
            {
                return response()->json([
                    'status'=>404,
                    'message'=>'No Student Found.'
                ]);
            }

        }
    }

    public function destroy($id)
    {
        $model = LanguageLine::find($id);
        if($model) {
            $model->delete();

            return response()->json([
                'status'=>200,
                'message'=>'Student Deleted Successfully.'
            ]);
        } else {
            return response()->json([
                'status'=>404,
                'message'=>'No Student Found.'
            ]);
        }
    }

    public function search($query)
    {
        $models = LanguageLine::where('group', 'like', '%'.$query.'%')
            ->orWhere('key', 'like', '%'.$query.'%')
            //->orWhere('text', 'like', '%'.$query.'%')
            //->orWhere('meta->text', 'LIKE', '%'.$query.'%')
            //->orWhereJsonContains('text', [['uz' ,'LIKE', '%'.$query.'%']])
            ->orderBy('group')
            ->paginate(25);

        return response()->json([
            'status'=>200,
            'models'=> $models,
        ]);
    }
}
