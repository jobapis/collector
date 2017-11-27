<?php namespace Tests\Unit\Jobs;

use JobApis\Collector\Clients\AlgoliaJobsClient;
use JobApis\Collector\Jobs\SaveListings;
use JobApis\Jobs\Client\Collection;
use Mockery as m;
use Tests\TestCase;

class SaveListingsTest extends TestCase
{
    public $algolia;
    public $listings;
    public $term;

    public function setUp()
    {
        $this->listings = m::mock(Collection::class);
        $this->term = [
            'index' => uniqid(),
            'tag' => uniqid(),
        ];
        $this->job = new SaveListings($this->listings, $this->term);
        $this->algolia = m::mock(AlgoliaJobsClient::class);
    }

    public function testItCanHandleJobWhenIndexSet()
    {
        $listing = (object) [
            'source' => uniqid(),
            'sourceId' => uniqid(),
            'url' => uniqid(),
        ];
        $listings = [$listing, $listing, $listing];

        $this->algolia->shouldReceive('setIndex')
            ->with($this->term['index'])
            ->once()
            ->andReturn(true);
        $this->listings->shouldReceive('all')
            ->once()
            ->andReturn($listings);
        $this->algolia->shouldReceive('addOrUpdateObjects')
            //->with($listings)
            ->andReturn(null);

        $this->job->handle($this->algolia);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp "Index '.*' not valid."
     */
    public function testItThrowsErrorWhenIndexInvalid()
    {
        $this->algolia->shouldReceive('setIndex')
            ->with($this->term['index'])
            ->once()
            ->andReturn(false);

        $this->job->handle($this->algolia);
    }

}
