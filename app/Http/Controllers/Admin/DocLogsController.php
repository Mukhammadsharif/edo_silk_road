<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExportDocLog;
use App\Http\Controllers\Controller;
use App\Models\Admin\DocLog;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocLogsController extends Controller
{
    //
    public function index(Request $request): View|Factory|Application
    {
        //
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        $searchModels = DocLog::where('id', '>', 0);

        if ($from_date && $to_date) {
            $from_date = date('Y-m-d', strtotime($from_date));
            $to_date = date('Y-m-d', strtotime($to_date));
            $searchModels->whereBetween('act_date', [$from_date, $to_date]);
        }

        $models = $searchModels->orderBy('id', 'DESC')->paginate(25);

        $models->appends([
            'from_date' => $from_date,
            'to_date' => $to_date
        ]);

        return view('admin.doc-log.index', compact('models', 'from_date', 'to_date'))
            ->with('i', (request()->input('page', 1) - 1) * 25);
    }

    public function exportDocLogs(Request $request): BinaryFileResponse
    {
        $from = $request->from_date;
        $to = $request->to_date;

        $model = DocLog::whereBetween('act_date', [$from, $to])->get();

        $excelHeader = [];

        $n = 1;

        $excelHeader[] = [
            'ID',
            'Hujjat turi',
            'Hujjat nomi',
            'Harakat turi',
            'Harakat vaqti',
            'Xodim FIO',
            'Bo`lim',
            'Lavozimi',
            'Kompyuter IP',
            'yaratilgan sana'
        ];

        foreach ($model as $value) $excelHeader[] = $this->assignData($n++,$value);

        return Excel::download(new ExportDocLog($excelHeader), 'edo_logs.xlsx');
    }

    protected function assignData($n, $value)
    {
        $actionName = $value->actType->title??'-';
        $performerLName = $value->userWorkHistory->personal->l_name??'-';
        $performerFName = $value->userWorkHistory->personal->f_name??'-';
        $performerDepartment = $value->userWorkHistory->department->name_uz??'-';
        $performerPosition = $value->userWorkHistory->userPost->name_uz??'-';
        return [
            'documentID'=>$value->doc_id,
            'documentType'=>$value->log_type,
            'documentName'=>$value->doc_name,
            'actionName'=>$actionName,
            'actionDate'=>$value->act_date,
            'performer'=>$performerLName.' '.$performerFName,
            'performerDepartment'=>$performerDepartment,
            'performerPosition'=>$performerPosition,
            'computerIP'=>$value->computer_id,
            'created_at'=>$value->created_at->format('Y-m-d H:i')
        ];
    }
}
