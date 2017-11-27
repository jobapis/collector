<?php namespace Tests\Unit\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use JobApis\Collector\Clients\ManagementApiClient;
use Tests\TestCase;
use Mockery as m;

class ManagementApiClientTest extends TestCase
{
    /**
     * @var ManagementApiClient
     */
    public $client;

    public function setUp()
    {
        $this->client = new ManagementApiClient();
        $this->client->client = m::mock(Client::class);
    }

    public function testItCanGetTermsAsArray()
    {
        $options = [
            'option1' => uniqid(),
            'option2' => uniqid(),
        ];
        $response = m::mock(Response::class);
        $responseArray = [
            'id' => uniqid(),
        ];

        $this->client->client->shouldReceive('get')
            ->with('terms', ['query' => $options])
            ->once()
            ->andReturn($response);
        $response->shouldReceive('getBody')
            ->once()
            ->andReturnSelf();
        $response->shouldReceive('getContents')
            ->once()
            ->andReturn(json_encode($responseArray));

        $results = $this->client->getTerms($options);

        $this->assertEquals($responseArray, $results);
    }

    /**
     * @expectedException \Exception
     */
    public function testItReturnsFalseWhenExceptionThrown()
    {
        $message = uniqid();

        $this->client->client->shouldReceive('get')
            ->with('terms', ['query' => []])
            ->once()
            ->andThrow(new \Exception($message));

        $results = $this->client->getTerms();
    }

    public function testItCanUpdateTerms()
    {
        $id = uniqid();
        $data = [
            'location' => uniqid(),
            'query' => uniqid(),
        ];
        $response = m::mock(Response::class);
        $responseArray = [
            'id' => $id,
        ];

        $this->client->client->shouldReceive('put')
            ->with('terms/'.$id, ['json' => $data])
            ->once()
            ->andReturn($response);
        $response->shouldReceive('getBody')
            ->once()
            ->andReturnSelf();
        $response->shouldReceive('getContents')
            ->once()
            ->andReturn(json_encode($responseArray));

        $results = $this->client->updateTerm($id, $data);

        $this->assertEquals($responseArray, $results);
    }
}