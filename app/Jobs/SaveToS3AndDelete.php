<?php namespace JobApis\Collector\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use JobApis\Collector\Clients\Interfaces\SearchIndexInterface;
use Ramsey\Uuid\Uuid;

class SaveToS3AndDelete implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * @var array
     */
    public $listings;

    /**
     * Create a new job instance.
     */
    public function __construct($listings = [])
    {
        $this->listings = $listings;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SearchIndexInterface $searchIndex) {
        $filename = date('Y-m-d-H-i').'-'.uniqid().'.json';

        // Save to s3
        if(Storage::disk('s3')->put(env('APP_ENV').'/'.$filename, json_encode($this->listings))) {
            // Delete on success
            $searchIndex->deleteObjects(array_map(function ($object) {
                return $object['objectID'];
            }, $this->listings));
        }
    }
}
