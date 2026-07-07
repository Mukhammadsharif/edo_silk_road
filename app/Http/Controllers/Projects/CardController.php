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
        $this->requireAdmin();
        $this->authorizeCard($card);
        $card->delete();
        return response()->noContent();
    }

    public function full(Card $card)
    {
        $this->authorizeCard($card);
        $card->load([
            'members.personalInfo',
            'assignee.personalInfo',
            'checklists.items',
            'activities.user.personalInfo',
            'comments.user.personalInfo',
            'labels',
        ]);

        return response()->json([
            'id' => $card->id,
            'title' => $card->title,
            'description' => $card->description,
            'label_color' => $card->label_color,
            'priority' => $card->priority,
            'cover_color' => $card->cover_color,
            'due_date' => optional($card->due_date)->format('Y-m-d'),
            'members' => $card->members->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
            ]),
            'checklists' => $card->checklists->map(fn ($cl) => [
                'id' => $cl->id,
                'title' => $cl->title,
                'items' => $cl->items->map(fn ($i) => [
                    'id' => $i->id, 'title' => $i->title, 'is_done' => $i->is_done,
                ]),
            ]),
            'activities' => $card->activities->map(fn ($a) => [
                'id' => $a->id,
                'description' => $a->description,
                'created_at' => $a->created_at,
                'user' => ['id' => $a->user?->id, 'name' => $a->user?->name],
            ]),
            'comments' => $card->comments->map(fn ($c) => [
                'id' => $c->id,
                'body' => $c->body,
                'created_at' => $c->created_at,
                'user' => ['id' => $c->user?->id, 'name' => $c->user?->name],
            ]),
        ]);
    }

    public function addMember(Request $request, Card $card)
    {
        $this->authorizeCard($card);
        $userId = $request->validate(['user_id' => 'required|exists:users,id'])['user_id'];
        $card->members()->syncWithoutDetaching([$userId]);

        $user = \App\Models\User::with('personalInfo')->find($userId);
        $card->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'member_added',
            'description' => "добавил участника {$user->name}",   // ФИО через аксессор
        ]);

        // вернуть участников с ФИО
        return response()->json(
            $card->members()->with('personalInfo')->get()->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
            ])
        );
    }
    public function attachLabel(Card $card, \App\Models\Projects\BoardLabel $label)
    {
        $this->authorizeCard($card);
        $card->labels()->syncWithoutDetaching([$label->id]);
        return response()->json($card->labels);
    }

    public function detachLabel(Card $card, \App\Models\Projects\BoardLabel $label)
    {
        $this->authorizeCard($card);
        $card->labels()->detach($label->id);
        return response()->json($card->labels);
    }
    public function removeMember(Card $card, \App\Models\User $user)
    {
        $this->authorizeCard($card);
        $card->members()->detach($user->id);
        $card->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'member_removed',
            'description' => "удалил участника {$user->name}",   // ФИО через аксессор
        ]);
        return response()->json(
            $card->members()->with('personalInfo')->get()->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
            ])
        );
    }



    public function boardUsers(Request $request)
    {
        $boardId = $request->query('board_id');
        $board = Board::findOrFail($boardId);

        return response()->json(
            $board->members()
                ->with('personalInfo')
                ->get(['users.id', 'username'])
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
        );
    }

    public function allOrgUsers()
    {
        $orgId = auth()->user()->org_unit_id;

        return \App\Models\User::query()
            ->with('personalInfo')
            ->when($orgId, fn ($q) => $q->where('org_unit_id', $orgId))
            ->when(! $orgId, fn ($q) => $q->where('id', auth()->id()))
            ->get(['id', 'username'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name]);
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
