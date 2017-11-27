<?php namespace JobApis\Collector\Jobs;

use Illuminate\Bus\Dispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use JobApis\Collector\Clients\Interfaces\SearchIndexInterface;

class ArchiveListings implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * Number of results per page per API
     *
     * @var int
     */
    const RESULTS_PER_PAGE = 500;

    /**
     * Max Number of results to remove at once
     *
     * @var int
     */
    const MAX_RESULTS_PER_ARCHIVE = 30000;

    /**
     * @var integer
     */
    public $beforeTime;

    /**
     * Create a new job instance.
     */
    public function __construct($beforeTime)
    {
        $this->beforeTime = $beforeTime;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        SearchIndexInterface $searchIndex,
        Dispatcher $dispatcher
    ) {
        $results = [];
        $cc = 0;
        foreach($searchIndex->browse('', [
            'numericFilters' => ['lastObservedAt<' . $this->beforeTime]
        ]) as $result) {
            $cc++;
            $results[] = $result;
            if (count($results) === self::RESULTS_PER_PAGE) {
                // Create job to send each page to Amazon S3 and delete when successful
                $dispatcher->dispatch(new SaveToS3AndDelete($results));
                $results = [];
            }
            // Don't remove > 10k at once
            if ($cc >= self::MAX_RESULTS_PER_ARCHIVE) { break; }
        }
    }
}
