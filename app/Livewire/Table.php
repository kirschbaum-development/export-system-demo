<?php

namespace App\Livewire;

use App\Jobs\ExportJob;
use App\Models\Team;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function export(): void
    {
        dispatch(new ExportJob(
            query: $this->getQuery()->getQuery(),
            header: ['name', 'email', 'role'],
            mapper: fn (User $user): array => [
                $user->name,
                $user->email,
                $user->role,
            ],
            user: auth()->user(),
        ));

        Notification::make()
            ->title('Export started')
            ->info()
            ->send();
    }

    public function getQuery(): BelongsToMany
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
