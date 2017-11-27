<?php namespace Tests\Unit\Clients;

use AlgoliaSearch\Client;
use AlgoliaSearch\Index;
use JobApis\Collector\Clients\AlgoliaJobsClient;
use Tests\TestCase;
use Mockery as m;

class AlgoliaApiClientTest extends TestCase
{
    /**
     * @var AlgoliaJobsClient
     */
    public $client;

    public function setUp()
    {
        $this->client = new AlgoliaJobsClient();
        $this->client->client = m::mock(Client::class);
        $this->client->index = m::mock(Index::class);
    }

    public function testItCanAddObjectsWhenNotExist()
    {
        $objects = [
            $this->getJobObject(),
            $this->getJobObject(),
            $this->getJobObject(),
        ];
        $apiResults = [];

        // What the result object array should look like
        $resultObjects = array_map(function ($object) {
            $object['firstObservedAt'] = $object['lastObservedAt'];
            return $object;
        }, $objects);

        $this->client->index->shouldReceive('search')
            ->times(count($objects))
            ->andReturn(['hits' => $apiResults]);
        $this->client->index->shouldReceive('addObjects')
            ->with($resultObjects)
            ->once()
            ->andReturn(true);

        $results = $this->client->addOrUpdateObjects($objects);

        $this->assertEquals([], $results['toUpdate']);
        $this->assertEquals($resultObjects, $results['toAdd']);
    }

    public function testItCanUpdateObjectsWhenExist()
    {
        $objects = [
            $this->getJobObject(),
            $this->getJobObject(),
            $this->getJobObject(),
        ];
        $apiResults = [
            $this->getApiJobObject(),
        ];

        // What the resulting object array should look like
        $resultObjects = array_map(function ($object) use ($apiResults) {
            $object['_tags'] = [$apiResults[0]['_tags'][0], $object['_tags'][0]];
            $object['firstObservedAt'] = $apiResults[0]['firstObservedAt'];
            $object['objectID'] = $apiResults[0]['objectID'];
            return $object;
        }, $objects);

        $this->client->index->shouldReceive('search')
            ->times(count($objects))
            ->andReturn(['hits' => $apiResults]);
        $this->client->index->shouldReceive('saveObjects')
            ->with($resultObjects)
            ->once()
            ->andReturn(true);

        $results = $this->client->addOrUpdateObjects($objects);

        $this->assertEquals($resultObjects, $results['toUpdate']);
        $this->assertEquals([], $results['toAdd']);
    }

    public function testItSearchesAlgoliaWithExpectedString()
    {
        $objects = [$this->getJobObject()];
        $options = [
            'hitsPerPage' => 1,
            'facetFilters' => [
                'source:'.$objects[0]['source']
            ],
            'disableTypoToleranceOnAttributes' => 'sourceId,url',
            'restrictSearchableAttributes' => 'sourceId',
        ];

        $this->client->index->shouldReceive('search')
            ->with($objects[0]['sourceId'], $options)
            ->once()
            ->andReturn(['hits' => []]);
        $this->client->index->shouldReceive('addObjects')
            ->once()
            ->andReturn(true);

        $this->client->addOrUpdateObjects($objects);
    }

    private function getJobObject()
    {
        return [
            'lastObservedAt' => uniqid(),
            'source' => uniqid(),
            'sourceId' => uniqid(),
            '_tags' => [uniqid()],
        ];
    }

    private function getApiJobObject()
    {
        return [
            'firstObservedAt' => uniqid(),
            'lastObservedAt' => uniqid(),
            'objectID' => uniqid(),
            'source' => uniqid(),
            'sourceId' => uniqid(),
            '_tags' => [uniqid()],
        ];
    }
}