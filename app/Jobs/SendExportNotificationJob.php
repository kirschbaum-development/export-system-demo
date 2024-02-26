<?php

namespace App\Jobs;

use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendExportNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $fileName,
        protected User $user,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $csvUrl = Storage::temporaryUrl("exports/{$this->fileName}", now()->addDay());

        Notification::make()
            ->title('Export completed')
            ->actions([
                Action::make('download')->url($csvUrl),
            ])
            ->success()
            ->sendToDatabase($this->user);
    }
}
