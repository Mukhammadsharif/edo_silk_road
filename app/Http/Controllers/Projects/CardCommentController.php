<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Projects\Card;
use App\Models\Projects\CardComment;
use Illuminate\Http\Request;

class CardCommentController extends Controller
{
    public function store(Request $request, Card $card)
    {
        abort_unless(\App\Models\Projects\Board::whereKey($card->list->board_id)->exists(), 404);

        $comment = $card->comments()->create(
            $request->validate(['body' => 'required|string'])
        );

        return response()->json($comment->load('user'));
    }

    public function destroy(CardComment $comment)
    {
        abort_unless($comment->user_id === auth()->id(), 403);
        $comment->delete();
        return response()->noContent();
    }
}
