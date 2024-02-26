<?php

namespace App\Jobs;

use App\Models\User;
use Closure;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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

    protected SerializableClosure $mapper;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $model,
        protected array $header,
        protected array $records,
        Closure $mapper,
        protected User $user,
    ) {
        $this->mapper = new SerializableClosure($mapper);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $csv = Writer::createFromString();
        $csv->insertOne($this->header);
        $csv->insertAll($this->model::find($this->records)->map($this->mapper->getClosure())->all());

        $fileName = str($this->model)
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
