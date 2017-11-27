<?php namespace JobApis\Collector\Providers;

use Illuminate\Support\ServiceProvider;
use JobApis\Collector\Clients\AlgoliaJobsClient;
use JobApis\Collector\Clients\AlgoliaSearchTermsClient;
use JobApis\Collector\Clients\Interfaces\SearchIndexInterface;
use JobApis\Collector\Clients\Interfaces\SearchTermsInterface;
use JobApis\Jobs\Client\JobsMulti;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Use management API for search terms
        $this->app->bind(SearchTermsInterface::class, AlgoliaSearchTermsClient::class);

        // Use Algolia client for search index
        $this->app->bind(SearchIndexInterface::class, AlgoliaJobsClient::class);

        // Initialize JobsMulti
        $this->app->bind(JobsMulti::class, function () {
            return new JobsMulti(config('jobboards'));
        });
    }
}
