<?php namespace JobApis\Collector\Clients;

use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

class AlgoliaSearchTermsClient implements Interfaces\SearchTermsInterface
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var Index
     */
    public $index;

    public function __construct($index = null)
    {
        $this->client = new Client(
            env("ALGOLIA_APP_ID"),
            env("ALGOLIA_API_KEY")
        );
        $this->index = $index ?? $this->client->initIndex(env("TERMS_INDEX"));
    }

    /**
     * Get terms
     *
     * @param array $options
     *
     * @return array
     */
    public function getTerms($options = [])
    {
        return $this->index->search("*", $options);
    }

    /**
     * Update term by id
     *
     * @param string $id
     * @param array $data
     *
     * @return array
     */
    public function updateTerm($id = null, $data = [])
    {
        return $this->index->partialUpdateObject(
            array_merge($data, ['objectID' => $id])
        );
    }
}