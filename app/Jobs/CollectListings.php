<?php namespace JobApis\Collector\Jobs;

use Illuminate\Bus\Dispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use JobApis\Collector\Clients\Interfaces\SearchTermsInterface;
use JobApis\Jobs\Client\JobsMulti;

class CollectListings implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * Maximum age of each listing (in days)
     *
     * @var int
     */
    const MAX_AGE = 7;

    /**
     * Maximum number of listings to get from all APIs
     *
     * @var int
     */
    const RESULTS_PER_TERM = 50;

    /**
     * Number of results per page per API
     *
     * @var int
     */
    const RESULTS_PER_PAGE = 20;

    /**
     * @var array
     */
    public $term;

    /**
     * Create a new job instance.
     */
    public function __construct($term = [])
    {
        $this->term = $term;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        JobsMulti $jobsClient,
        Dispatcher $dispatcher,
        SearchTermsInterface $searchTerms
    ) {
        // Add a keyword
        $jobsClient->setKeyword($this->term['keyword']);
        // Add a location if it exists
        if ($this->term['location']) {
            $jobsClient->setLocation($this->term['location']);
        }

        // Get the latest listings
        $listings = $jobsClient->setPage(1, self::RESULTS_PER_PAGE)
            ->setOptions([
                'maxAge' => self::MAX_AGE,
                'maxResults' => self::RESULTS_PER_TERM,
                'order' => 'desc',
                'orderBy' => 'datePosted',
            ])
            ->getAllJobs();

        // Dispatch a job to save them
        $dispatcher->dispatch(new SaveListings($listings, $this->term));

        // Update the term's collection completed date
        $searchTerms->updateTerm($this->term['objectID'], [
            'last_collection_completed_at' => time(),
        ]);
    }
}
