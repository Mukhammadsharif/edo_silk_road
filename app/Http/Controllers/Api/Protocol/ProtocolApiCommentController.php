<?php

namespace App\Http\Controllers\Api\Protocol;

use App\Http\Controllers\Controller;
use App\Models\Protocol\ProtocolDocComments;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class ProtocolApiCommentController extends Controller
{
    /**
     * Проверить, является ли текущий пользователь участником протокола.
     */
    private function userHasAccessToProtocol(int $documentId): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $workIds = $user->userWorkIDs();
        if (empty($workIds)) {
            return false;
        }

        return ProtocolDocMember::where('doc_id', $documentId)
            ->whereIn('user_id', $workIds)
            ->exists();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/protocol/{doc_hash}/comments",
     *     summary="Список комментариев к протоколу",
     *     tags={"Protocols"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="doc_hash", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Список комментариев"),
     *     @OA\Response(response=403, description="Доступ запрещён"),
     *     @OA\Response(response=404, description="Протокол не найден")
     * )
     */
    public function index($doc_hash)
    {
        try {
            $document = ProtocolDocument::where('doc_hash', $doc_hash)->firstOrFail();

            if (!$this->userHasAccessToProtocol($document->id)) {
                Log::warning('Access denied to protocol comments', [
                    'user_id'  => Auth::id(),
                    'doc_hash' => $doc_hash,
                    'doc_id'   => $document->id,
                ]);
                return response()->json(['message' => 'Access denied'], 403);
            }

            $comments = ProtocolDocComments::where('doc_id', $document->id)
                ->with(['creator.personal', 'replies'])
                ->orderByDesc('created_date')
                ->get();

            return response()->json($comments, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Protocol not found'], 404);
        } catch (Exception $e) {
            Log::error('Error fetching comments', [
                'doc_hash' => $doc_hash,
                'error'    => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error loading comments'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/protocol/protocol-comments/{comment_id}",
     *     summary="Просмотр одного комментария",
     *     tags={"Protocols"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="comment_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Комментарий"),
     *     @OA\Response(response=403, description="Доступ запрещён"),
     *     @OA\Response(response=404, description="Не найден")
     * )
     */
    public function show($comment_id)
    {
        try {
            $comment = ProtocolDocComments::with(['creator.personal', 'document'])
                ->findOrFail($comment_id);

            if (!$this->userHasAccessToProtocol($comment->doc_id)) {
                Log::warning('Access denied to protocol comment', [
                    'user_id'    => Auth::id(),
                    'comment_id' => $comment_id,
                    'doc_id'     => $comment->doc_id,
                ]);
                return response()->json(['message' => 'Access denied'], 403);
            }

            return response()->json($comment, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Comment not found'], 404);
        } catch (Exception $e) {
            Log::error('Error fetching protocol comment', [
                'comment_id' => $comment_id,
                'error'      => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error loading comment'], 500);
        }
    }
}
