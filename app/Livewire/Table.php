<?php

namespace App\Livewire;

use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        auth()->login(User::first());
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function getQuery()
    {
        $team = Team::first();

        $search = Str::lower($this->search);

        return $team->users()->when(
            filled($search),
            fn (Builder $query) => $query->where(
                fn (Builder $query) => $query
                    ->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%"),
            ),
        );
    }

    public function render(): View
    {
        return view('livewire.table', [
            'users' => $this->getQuery()->simplePaginate(10),
        ]);
    }
}
