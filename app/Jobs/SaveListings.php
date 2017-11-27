<?php namespace JobApis\Collector\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use JobApis\Collector\Clients\Interfaces\SearchIndexInterface;
use JobApis\Jobs\Client\Collection;

class SaveListings implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * The search term from the management API
     *
     * @var array
     */
    public $term;

    /**
     * @var Collection
     */
    public $listings;

    /**
     * Create a new job instance.
     */
    public function __construct(Collection $listings, $term = [])
    {
        $this->listings = $listings;
        $this->term = $term;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SearchIndexInterface $searchIndex)
    {
        // Save the jobs
        $searchIndex->addOrUpdateObjects($this->appendFields());
    }

    /**
     * Appends fields to the listing for tracking purposes
     *
     * @return array
     */
    private function appendFields()
    {
        return array_map(function ($listing) {
            // Convert to anonymous object
            $listing = json_decode(json_encode($listing));

            // Append tag and observed at fields
            $listing->_terms = [$this->term['objectID']];
            $listing->lastObservedAt = time();

            return $listing;
        }, $this->listings->all());
    }
}
