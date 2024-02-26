<?php

namespace App\Jobs;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use App\Models\User;
use Closure;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use League\Csv\Writer;

class ExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $query;

    protected SerializableClosure $mapper;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Builder $query,
        protected array $header,
        protected array $records,
        Closure $mapper,
        protected User $user,
    ) {
        $this->query = EloquentSerializeFacade::serialize($query);
        $this->mapper = new SerializableClosure($mapper);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $query = EloquentSerializeFacade::unserialize($this->query);

        $csv = Writer::createFromString();
        $csv->insertOne($this->header);
        $csv->insertAll($query->find($this->records)->map($this->mapper->getClosure())->all());

        $fileName = str($query->getModel()::class)
            ->classBasename()
            ->plural()
            ->snake()
            ->append('-')
            ->append(Str::random())
            ->append('.csv');

        Storage::put("exports/{$fileName}", $csv->toString(), options: 'private');

        $csvUrl = Storage::temporaryUrl("exports/{$fileName}", now()->addDay());

        Notification::make()
            ->title('Export completed')
            ->actions([
                Action::make('download')->url($csvUrl),
            ])
            ->success()
            ->sendToDatabase($this->user);
    }
}
