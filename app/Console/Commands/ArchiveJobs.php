<?php namespace JobApis\Collector\Console\Commands;

use Illuminate\Console\Command;
use JobApis\Collector\Jobs\ArchiveListings;

class ArchiveJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collector:archive {daysBack=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archives job listings from Algolia to S3';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Calculate the time to start
        $beforeTime = time() - (60 * 60 * 24 * $this->argument('daysBack'));

        dispatch(new ArchiveListings($beforeTime));

        $this->info("All posts before " . date('Y-m-d', $beforeTime). " queued for archival.");
    }
}
