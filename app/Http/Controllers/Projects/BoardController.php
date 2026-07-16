<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Projects\Board;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    private function requireAdmin(): void
    {
        abort_unless(
            auth()->user()?->hasRole(['Super_Admin', 'it_admin']),
            403,
            'Недостаточно прав'
        );
    }

    private function requireKanbanAccess(): void
    {
        abort_unless(
            auth()->user()?->hasKanbanAccess(),
            403,
            'Модуль Kanban не подключён к вашей организации'
        );
    }

    public function index()
    {
//        $this->requireKanbanAccess();

        $user = auth()->user();

        $query = Board::query()->where('is_archived', false);

        if ($user->hasRole('Super_Admin')) {
            // глобальный админ — видит всё, без фильтров
        } elseif ($user->hasRole('IT_Admin')) {
            // админ компании — все доски своей организации
            $query->where('org_id', $user->org_unit_id);
        } else {
            // обычный пользователь — в своей организации:
            // открытые доски ИЛИ те, где он руководитель/участник
            $query->where('org_id', $user->org_unit_id)
                ->where(function ($q) use ($user) {
                    $q->where('visibility', 'public')
                        ->orWhere('manager_id', $user->id)
                        ->orWhereHas('members', fn ($m) => $m->where('users.id', $user->id));
                });
        }

        $boards = $query
            ->withCount('lists')
            ->with([
                'lists' => fn ($q) => $q->withCount('cards'),
                'manager.personalInfo',
                'organization',
            ])
            ->latest('updated_at')
            ->get()
            ->map(function ($board) {
                $board->cards_total = $board->lists->sum('cards_count');
                return $board;
            });

        return view('boards.index', compact('boards'));
    }

    public function create()
    {
        $this->requireKanbanAccess();

        return view('boards.create');
    }

    public function store(Request $request)
    {
        $this->requireKanbanAccess();

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'manager_id'  => 'nullable|exists:users,id',
        ]);

        $board = Board::create($data);

        foreach (['К исполнению', 'В работе', 'На согласовании', 'Завершено'] as $i => $title) {
            $board->lists()->create(['title' => $title, 'position' => $i]);
        }

        if ($request->expectsJson()) {
            return response()->json($board);
        }
        return redirect()->route('boards.show', $board);
    }

    public function show(Board $board)
    {
//        $this->requireKanbanAccess();

        $user = auth()->user();

        if ($user->hasRole('Super_Admin')) {
            // доступ ко всему
        } elseif ($user->hasRole('IT_Admin')) {
            // только своя компания
            abort_unless($board->org_id === $user->org_unit_id, 403, 'Нет доступа');
        } else {
            // своя компания + (руководитель или участник)
            $sameOrg   = $board->org_id === $user->org_unit_id;
            $isManager = $board->manager_id === $user->id;
            $isMember  = $board->members()->where('users.id', $user->id)->exists();
            abort_unless($sameOrg && ($isManager || $isMember), 403, 'Нет доступа к этому проекту');
        }
        $board->load([
            'manager.personalInfo',
            'lists.cards' => fn ($q) => $q->withCount('comments')
                ->with(['checklists.items', 'assignee.personalInfo'])
                ->orderBy('position'),
        ]);

        // метрики шапки (как было)
        $board->lists_total = $board->lists->count();
        $board->cards_total = $board->lists->sum(fn ($l) => $l->cards->count());
        $doneTitles = ['Завершено', 'Выполнено', 'Готово'];
        $board->cards_done = $board->lists
            ->filter(fn ($l) => in_array($l->title, $doneTitles))
            ->sum(fn ($l) => $l->cards->count());
        $board->member_avatars = $board->lists
            ->flatMap(fn ($l) => $l->cards->pluck('assignee'))
            ->filter()->unique('id')->values();
        $board->load([
            'labels',
            'manager',
            'lists.cards' => fn ($q) => $q->withCount('comments')->with(['checklists.items', 'assignee'])->orderBy('position'),
        ]);
        return view('boards.show', compact('board'));
    }

    public function edit(Board $board)
    {
        $this->requireKanbanAccess();

        return view('boards.edit', compact('board'));
    }

    public function update(Request $request, Board $board)
    {
        $this->requireKanbanAccess();
        $this->requireAdmin();

        $data = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'manager_id'  => 'nullable|exists:users,id',
            'visibility'  => 'nullable|in:private,public',
            'is_archived' => 'boolean',
        ]);

        $board->update($data);

        if ($request->expectsJson()) {
            return response()->json($board->load('manager'));
        }
        return back();
    }

    // участники доски (для отображения в настройках)
    public function members(Board $board)
    {
        $this->requireKanbanAccess();

        return response()->json(
            $board->members()->with('personalInfo')->get(['users.id', 'username'])
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
        );
    }

    public function addMember(Request $request, Board $board)
    {
        $this->requireKanbanAccess();
        $this->requireAdmin();

        $userId = $request->validate(['user_id' => 'required|exists:users,id'])['user_id'];
        $board->members()->syncWithoutDetaching([$userId]);

        return response()->json(
            $board->members()
                ->with('personalInfo')
                ->get(['users.id', 'username'])
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
        );
    }

    public function removeMember(Board $board, \App\Models\User $user)
    {
        $this->requireKanbanAccess();
        $this->requireAdmin();

        $board->members()->detach($user->id);

        // заодно снять его с карточек этой доски
        $cardIds = $board->lists()->with('cards')->get()
            ->flatMap(fn ($l) => $l->cards->pluck('id'));
        \DB::table('card_user')->whereIn('card_id', $cardIds)->where('user_id', $user->id)->delete();

        return response()->json(
            $board->members()
                ->with('personalInfo')
                ->get(['users.id', 'username'])
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
        );
    }

    public function destroy(Board $board)
    {
        $this->requireKanbanAccess();
        $this->requireAdmin();

        $board->delete();
        return redirect()->route('boards.index');
    }
}
