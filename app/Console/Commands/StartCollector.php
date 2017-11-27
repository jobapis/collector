<?php namespace JobApis\Collector\Console\Commands;

use Illuminate\Console\Command;
use JobApis\Collector\Clients\Interfaces\SearchTermsInterface;
use JobApis\Collector\Jobs\CollectListings;

class StartCollector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collector:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts the job listing collection process.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(SearchTermsInterface $searchTerms)
    {
        // Get the next search term
        $nextTerm = $searchTerms->getTerms(['hitsPerPage' => 1])['hits'][0];

        if ($nextTerm) {
            // Update the term's collection start date
            $searchTerms->updateTerm($nextTerm['objectID'], [
                'last_collection_requested_at' => time()
            ]);

            // Dispatch the CollectListings job
            dispatch(new CollectListings($nextTerm));

            // Output to the console
            $this->info("Search #{$nextTerm['objectID']} queued for collection.");
        } else {
            $this->info("No search terms found.");
        }
    }
}
