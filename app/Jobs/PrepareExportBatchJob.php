<?php

namespace App\Jobs;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laravel\SerializableClosure\SerializableClosure;

class PrepareExportBatchJob implements ShouldQueue
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

        $keyName = $query->getModel()->getKeyName();
        $qualifiedKeyName = $query->getModel()->qualifyColumn($keyName);

        $baseQuery = $query->toBase();
        $baseQuery->distinct($qualifiedKeyName);

        $page = 1;

        $baseQuery
            ->select([$qualifiedKeyName])
            ->chunkById(
                100,
                function (Collection $records) use ($keyName, &$page) {
                    $this->batch()->add(new ExportChunkJob(
                        query: $this->query,
                        records: Arr::pluck($records->all(), $keyName),
                        page: $page,
                        mapper: $this->mapper,
                        fileName: $this->fileName,
                    ));

                    $page++;
                },
                column: $qualifiedKeyName,
                alias: $keyName,
            );
    }
}
