<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Projects\Board;
use App\Models\Projects\BoardList;
use App\Models\Projects\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    public function store(Request $request, BoardList $list)
    {
        $this->authorizeList($list);
        $card = $list->cards()->create([
            'title'    => $request->validate(['title' => 'required|string|max:255'])['title'],
            'position' => ($list->cards()->max('position') ?? -1) + 1,
        ]);

        $card->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'created',
            'description' => "добавил карточку в список «{$list->title}»",
        ]);

        return response()->json($card->load('assignee'));
    }

    public function show(Card $card)
    {
        $this->authorizeCard($card);
        $card->load(['assignee', 'comments.user']);
        return response()->json($card);
    }

    public function update(Request $request, Card $card)
    {
        $this->authorizeCard($card);

        $card->update($request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'label_color' => 'nullable|string|max:20',
            'assigned_to' => 'nullable|exists:users,id',
            'priority'    => 'nullable|in:low,medium,high',
            'cover_color' => 'nullable|string|max:20',
        ]));

        return response()->json($card->load('assignee'));
    }

    public function move(Request $request, Card $card)
    {
        $this->authorizeCard($card);

        $data = $request->validate([
            'board_list_id' => 'required|exists:board_lists,id',
            'ordered_ids'   => 'required|array',
            'ordered_ids.*' => 'integer|exists:cards,id',
        ]);

        // Целевой список должен принадлежать той же доске
        $targetList = BoardList::findOrFail($data['board_list_id']);
        abort_unless($targetList->board_id === $card->list->board_id, 403);

        DB::transaction(function () use ($card, $data) {
            $card->update(['board_list_id' => $data['board_list_id']]);

            foreach ($data['ordered_ids'] as $position => $id) {
                Card::where('id', $id)
                    ->where('board_list_id', $data['board_list_id'])
                    ->update(['position' => $position]);
            }
        });

        return response()->noContent();
    }

    public function destroy(Card $card)
    {
        $this->authorizeCard($card);
        $card->delete();
        return response()->noContent();
    }

    public function full(Card $card)
    {
        $this->authorizeCard($card);
        $card->load(['members', 'assignee', 'checklists.items', 'activities.user', 'comments.user']);
        return response()->json($card);   // карточка целиком, с id
    }

    public function addMember(Request $request, Card $card)
    {
        $this->authorizeCard($card);
        $userId = $request->validate(['user_id' => 'required|exists:users,id'])['user_id'];
        $card->members()->syncWithoutDetaching([$userId]);

        $user = \App\Models\User::find($userId);
        $card->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'member_added',
            'description' => "добавил участника {$user->username}",   // было name
        ]);

        // вернуть участников с ключом name для фронта
        return response()->json($card->members->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->username,
        ]));
    }

    public function removeMember(Card $card, \App\Models\User $user)
    {
        $this->authorizeCard($card);
        $card->members()->detach($user->id);

        return response()->json($card->members->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->username,
        ]));
    }



    public function boardUsers(Request $request)
    {
        // нужен board_id — берём через карточку или напрямую
        $boardId = $request->query('board_id');
        $board = Board::findOrFail($boardId);
        return response()->json($board->members()->get(['users.id', 'username']));
    }

    public function allOrgUsers()
    {
        $orgId = auth()->user()->org_id;
        return \App\Models\User::query()
            ->when($orgId, fn ($q) => $q->where('org_id', $orgId))
            ->when(! $orgId, fn ($q) => $q->where('id', auth()->id()))
            ->get(['id', 'username']);
    }
    private function authorizeList(BoardList $list): void
    {
        abort_unless(Board::whereKey($list->board_id)->exists(), 404);
    }

    private function authorizeCard(Card $card): void
    {
        abort_unless(Board::whereKey($card->list->board_id)->exists(), 404);
    }
}
