<?php namespace Tests\Unit\Jobs;

use Illuminate\Bus\Dispatcher;
use JobApis\Collector\Clients\ManagementApiClient;
use JobApis\Collector\Jobs\CollectListings;
use JobApis\Jobs\Client\Collection;
use JobApis\Jobs\Client\JobsMulti;
use Tests\TestCase;
use Mockery as m;

class CollectListingsTest extends TestCase
{
    public function testItCanHandleJobWhenNoLocation()
    {
        $this->term = [
            'keyword' => uniqid(),
            'location' => null,
            'id' => uniqid(),
            'index' => uniqid(),
        ];
        $this->job = new CollectListings($this->term);

        $jobsClient = m::mock(JobsMulti::class);
        $dispatcher = m::mock(Dispatcher::class);
        $managementApiClient = m::mock(ManagementApiClient::class);
        $listings = m::mock(Collection::class);

        $jobsClient->shouldReceive('setKeyword')
            ->with($this->term['keyword'])
            ->once()
            ->andReturnSelf();
        $jobsClient->shouldReceive('setPage')
            ->with(1, 20)
            ->once()
            ->andReturnSelf();
        $jobsClient->shouldReceive('setOptions')
            ->with([
                'maxAge' => 7,
                'maxResults' => 50,
                'order' => 'desc',
                'orderBy' => 'datePosted',
            ])
            ->once()
            ->andReturnSelf();
        $jobsClient->shouldReceive('getAllJobs')
            ->once()
            ->andReturn($listings);
        $dispatcher->shouldReceive('dispatch')
            ->once()
            ->andReturnSelf();
        $managementApiClient->shouldReceive('updateTerm')
            ->once()
            ->andReturnSelf();

        $this->job->handle($jobsClient, $dispatcher, $managementApiClient);
    }

    public function testItCanHandleJobWhenLocation()
    {
        $this->term = [
            'keyword' => uniqid(),
            'location' => uniqid(),
            'id' => uniqid(),
            'index' => uniqid(),
        ];
        $this->job = new CollectListings($this->term);

        $jobsClient = m::mock(JobsMulti::class);
        $dispatcher = m::mock(Dispatcher::class);
        $managementApiClient = m::mock(ManagementApiClient::class);
        $listings = m::mock(Collection::class);

        $jobsClient->shouldReceive('setKeyword')
            ->with($this->term['keyword'])
            ->once()
            ->andReturnSelf();
        $jobsClient->shouldReceive('setLocation')
            ->with($this->term['location'])
            ->once()
            ->andReturnSelf();
        $jobsClient->shouldReceive('setPage')
            ->with(1, 20)
            ->once()
            ->andReturnSelf();
        $jobsClient->shouldReceive('setOptions')
            ->with([
                'maxAge' => 7,
                'maxResults' => 50,
                'order' => 'desc',
                'orderBy' => 'datePosted',
            ])
            ->once()
            ->andReturnSelf();
        $jobsClient->shouldReceive('getAllJobs')
            ->once()
            ->andReturn($listings);
        $dispatcher->shouldReceive('dispatch')
            ->once()
            ->andReturnSelf();
        $managementApiClient->shouldReceive('updateTerm')
            ->once()
            ->andReturnSelf();

        $this->job->handle($jobsClient, $dispatcher, $managementApiClient);
    }
}
