<?php

namespace App\Http\Controllers\Api\Protocol;

use App\Http\Controllers\Controller;
use App\Models\Protocol\ProtocolDocComments;
use App\Models\Protocol\ProtocolDocument;
use Illuminate\Support\Facades\Log;
use Exception;

class ProtocolApiCommentController extends Controller
{
    /**
    * @OA\Get(
    *     path="/api/v1/protocol/{doc_hash}/comments",
    *     summary="Список комментариев к протоколу",
    *     tags={"Protocol Comments"},
    *     security={{"sanctum":{}}},
         *     @OA\Parameter(name="doc_hash", in="path", required=true, @OA\Schema(type="string")),
         *     @OA\Response(response=200, description="Список комментариев"),
         *     @OA\Response(response=404, description="Протокол не найден")
    * )
    */
    public function index($doc_hash)
{
    try {
        $document = ProtocolDocument::where('doc_hash', $doc_hash)->firstOrFail();

        $comments = ProtocolDocComments::where('doc_id', $document->id)
            ->with(['creator.personal', 'replies'])
            ->orderByDesc('created_date')
            ->get();

        return response()->json($comments, 200);

    } catch (Exception $e) {
        Log::error('Error fetching comments', ['doc_hash' => $doc_hash, 'error' => $e->getMessage()]);
        return response()->json(['message' => 'Error loading comments'], 500);
    }
}

    /**
     * @OA\Get(
     *     path="/api/v1/protocol/protocol-comments/{comment_id}",
     *     summary="Просмотр одного комментария",
     *     tags={"Protocol Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="comment_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Комментарий"),
     *     @OA\Response(response=404, description="Не найден")
     * )
     */
    public function show($comment_id)
{
    try {
        $comment = ProtocolDocComments::with(['creator.personal', 'document'])->findOrFail($comment_id);
        return response()->json($comment, 200);
    } catch (Exception $e) {
        return response()->json(['message' => 'Comment not found'], 404);
    }
}
}
