<?php

namespace App\Http\Controllers\File;

use App\Http\Controllers\Controller;
use App\Models\Document\DocumentFiles;
use App\Models\Document\ReplyDocumentFiles;
use App\Models\Protocol\ProtocolDocFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    //
    public function protocolDocFileDownload($id)
    {
        //
        $model = ProtocolDocFile::find($id);

        if ($model) {

            $path = '/'.$model->file_path.$model->file_hash;

            if (Storage::disk('ftp_13')->exists($path)){

                return Storage::disk('ftp_13')->download($path, $model->file_name);
            }

        }

        return back()->with('error', __('document_not_found'));
    }

    public function protocolDocFilePreView($id)
    {
        //
        $model = ProtocolDocFile::find($id);

        if ($model) {
            $path = '/'.$model->file_path.$model->file_hash;

            if (Storage::disk('ftp_13')->exists($path)){

                $res = Storage::disk('ftp_13')->get($path);

                if (strtoupper($model->file_ext) == 'PDF'){

                    return Response::make($res, 200, [
                        'Content-Type'        => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="'.$model->file_name.'"'
                    ]);

                } elseif (strtoupper($model->file_ext) == 'PNG' || strtoupper($model->file_ext) == 'JPG' ||
                    strtoupper($model->file_ext) == 'JPEG'){

                    return Response::make($res, 200, [
                        'Content-Type'        => 'image/png',
                        'Content-Disposition' => 'inline; filename="'.$model->file_name.'"'
                    ]);
                } else {

                    return Storage::disk('ftp_13')->download($path, $model->file_name);
                }
            }

        }

        return response()->json(__('document_not_found'));

    }

    public function protocolDocFileDelete($id)
    {
        $file = ProtocolDocFile::find($id);

        $res = 1;
        if ($file) {
            $path = '/'.$file->file_path.$file->file_hash;

            if (Storage::disk('ftp_13')->exists($path)){

                Storage::disk('ftp_13')->delete($path);
            }
            $file->delete();

            $res = 2;
        }

        return response()->json(['res' => $res, 'id' => $id, 'message' => __('message.task_completed_successfully')]);
    }

    public function download($id): StreamedResponse|RedirectResponse
    {
        //
        $model = ProtocolDocFile::find($id);

        if ($model) {

            $path = '/'.$model->file_path.$model->file_hash;

            if (Storage::disk('ftp_13')->exists($path)){

                return Storage::disk('ftp_13')->download($path, $model->file_name);
            }

        }

        return back()->with('error', __('document_not_found'));
    }

    public function fileDownload($id): StreamedResponse|RedirectResponse
    {
        //
        $model = DocumentFiles::find($id);

        if ($model) {
            $path = '/'.$model->file_path.$model->file_hash;

            if (Storage::disk('ftp_13')->exists($path)){

                return Storage::disk('ftp_13')->download($path, $model->file_name);
            }

        }

        return back()->with('error', __('document_not_found'));
    }

    public function preView($id): \Illuminate\Http\Response|StreamedResponse|JsonResponse
    {
        //
        $model = DocumentFiles::find($id);

        if ($model) {
            $path = '/'.$model->file_path.$model->file_hash;

            if (Storage::disk('ftp_13')->exists($path)){

                $res = Storage::disk('ftp_13')->get($path);

                if (strtoupper($model->file_ext) == 'PDF'){

                    return Response::make($res, 200, [
                        'Content-Type'        => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="'.$model->file_name.'"'
                    ]);

                } elseif (strtoupper($model->file_ext) == 'PNG' || strtoupper($model->file_ext) == 'JPG' ||
                    strtoupper($model->file_ext) == 'JPEG'){

                    return Response::make($res, 200, [
                        'Content-Type'        => 'image/png',
                        'Content-Disposition' => 'inline; filename="'.$model->file_name.'"'
                    ]);
                } else {

                    return Storage::disk('ftp_13')->download($path, $model->file_name);
                }
            }

        }

        return response()->json(__('document_not_found'));

    }

    public function downloadReply($id): StreamedResponse|RedirectResponse
    {
        //
        $model = ReplyDocumentFiles::find($id);
        if ($model) {

            $path = '/'.$model->file_path.$model->file_hash;

            if (Storage::disk('ftp_13')->exists($path)){

                return Storage::disk('ftp_13')->download($path, $model->file_name);
            }

        }

        return back()->with('error', __('document_not_found'));
    }

    public function preViewReply($id): \Illuminate\Http\Response|StreamedResponse|JsonResponse
    {
        //
        $model = ReplyDocumentFiles::find($id);

        if ($model) {

            $path = '/'.$model->file_path.$model->file_hash;

            if (Storage::disk('ftp_13')->exists($path)){

                $res = Storage::disk('ftp_13')->get($path);

                if (strtoupper($model->file_ext) == 'PDF'){

                    return Response::make($res, 200, [
                        'Content-Type'        => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="'.$model->file_name.'"'
                    ]);

                } elseif (strtoupper($model->file_ext) == 'PNG' || strtoupper($model->file_ext) == 'JPG' ||
                    strtoupper($model->file_ext) == 'JPEG'){

                    return Response::make($res, 200, [
                        'Content-Type'        => 'image/png',
                        'Content-Disposition' => 'inline; filename="'.$model->file_name.'"'
                    ]);
                } else {

                    return Storage::disk('ftp_13')->download($path, $model->file_name);
                }
            }
        }

        return response()->json(__('document_not_found'));

    }

    public function fileDelete($id): JsonResponse
    {
        $file = DocumentFiles::find($id);

        $res = 1;
        $message = __('unk');
        if ($file) {
            $path = '/'.$file->file_path.$file->file_hash;

            if (Storage::disk('ftp_13')->exists($path)){

                Storage::disk('ftp_13')->delete($path);
            }
            $file->delete();

            $res = 2;
            $message = __('file_succ_deleted');
        }

        return response()->json(['res' => $res, 'id' => $id, 'message' => $message]);
    }
}
