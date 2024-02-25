<?php

namespace App\Livewire;

use App\Models\Team;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use League\Csv\Writer;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function export(): StreamedResponse
    {
        $header = ['name', 'email', 'role'];
        $records = $this->getQuery()->get()->map(fn (User $user): array => [
            $user->name,
            $user->email,
            $user->pivot->role,
        ])->all();

        $csv = Writer::createFromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        Notification::make()
            ->title('Export completed')
            ->body('Downloading...')
            ->info()
            ->send();

        return response()->streamDownload(
            fn () => print($csv->toString()),
            'users.csv',
            ['Content-Type' => 'text/csv'],
        );
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
