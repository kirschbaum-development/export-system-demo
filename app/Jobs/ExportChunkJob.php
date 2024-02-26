<?php

namespace App\Jobs;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Laravel\SerializableClosure\SerializableClosure;
use League\Csv\Writer;

class ExportChunkJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $query,
        protected array $records,
        protected int $page,
        protected SerializableClosure $mapper,
        protected string $fileName,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $query = EloquentSerializeFacade::unserialize($this->query);

        $mapper = $this->mapper->getClosure();

        $csv = Writer::createFromString();
        $csv->insertAll($query->find($this->records)->map($mapper)->all());

        $paddedPageNumber = str_pad(strval($this->page), 16, '0', STR_PAD_LEFT);

        Storage::put("exports/{$this->fileName}/{$paddedPageNumber}.csv", $csv->toString(), Filesystem::VISIBILITY_PRIVATE);
    }
}
