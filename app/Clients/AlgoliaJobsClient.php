<?php namespace JobApis\Collector\Clients;

use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

class AlgoliaJobsClient implements Interfaces\SearchIndexInterface
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var array
     */
    public $splitOptions;

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
        $this->splitOptions = [
            'uniqueFields' => ['sourceId', 'url'],
            'facetFilters' => ['source'],
        ];
        $this->index = $index ?? $this->client->initIndex(env("JOBS_INDEX"));
    }

    /**
     * Add objects to the search index
     *
     * @param array $objects
     *
     * @return mixed
     */
    public function addObjects($objects = [])
    {
        return $this->index->addObjects($objects);
    }

    /**
     * Adds or updates each object depending on whether it's been added before
     *
     * @param array $objects
     * @param array $splitOptions
     *
     * @return mixed
     */
    public function addOrUpdateObjects($objects = [], $splitOptions = [])
    {
        // Split objects into two arrays of arrays: toAdd, toUpdate
        $objects = $this->prepareAddOrUpdateObjects(
            $objects,
            array_merge($this->splitOptions, $splitOptions)
        );

        // Update the existing ones
        if ($objects['toUpdate']) {
            $this->updateObjects($objects['toUpdate']);
        }

        // Save the new ones
        if ($objects['toAdd']) {
            $this->addObjects($objects['toAdd']);
        }

        return $objects;
    }

    /**
     * Browse the index
     *
     * @param string $query
     * @param array $options
     *
     * @return array
     */
    public function browse(string $query, $options = [])
    {
        return $this->index->browse($query, $options);
    }

    /**
     * Delete objects from the search index
     *
     * @param array $objects
     *
     * @return mixed
     */
    public function deleteObjects($objects = [])
    {
        return $this->index->deleteObjects($objects);
    }

    /**
     * Search for a specific term in the index
     *
     * @param string $query
     * @param array $options
     *
     * @return array
     */
    public function search(string $query, $options = [])
    {
        return $this->index->search($query, $options);
    }

    /**
     * Update objects in the search index
     *
     * @param array $objects
     *
     * @return mixed
     */
    public function updateObjects($objects = [])
    {
        return $this->index->saveObjects($objects);
    }

    /**
     * Splits objects
     *
     * @param array $objects
     * @param array $splitOptions
     *
     * @return array
     */
    private function prepareAddOrUpdateObjects($objects = [], $splitOptions = [])
    {
        $splitObjects = [
            'toAdd' => [],
            'toUpdate' => [],
        ];

        foreach ($objects as $object) {
            $object = (array) $object;
            $results = [];

            // Set options for query
            $options = $this->getSplitOptions($object, $splitOptions);

            foreach ($splitOptions['uniqueFields'] as $field) {
                if (isset($object[$field]) && $object[$field]) {
                    $options['restrictSearchableAttributes'] = $field;
                    $results = $this->index->search($object[$field], $options)['hits'];
                    break;
                }
            }

            if ($results && $results[0]) {
                $object['firstObservedAt'] = $results[0]['firstObservedAt'] ?? null;
                $object['_tags'] = $this->combineTags($results[0], $object);
                $object['objectID'] = $results[0]['objectID'];
                $splitObjects['toUpdate'][] = $object;
            } else {
                $object['firstObservedAt'] = $object['lastObservedAt'];
                $splitObjects['toAdd'][] = $object;
            }
        }

        return $splitObjects;
    }

    /**
     * Merges tags from the original array and the newly collected object
     *
     * @param array $original
     * @param array $new
     *
     * @return array|mixed
     */
    private function combineTags($original = [], $new = [])
    {
        try {
            // Check for the new tag in the original array
            if (!in_array($new['_tags'][0], $original['_tags'])) {
                // Add it to the array
                array_push($original['_tags'], $new['_tags'][0]);
            }
            return $original['_tags'];
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
        return [];
    }

    /**
     * Gets the search options for splitting updates/adds
     *
     * @param $object
     * @param $splitOptions
     *
     * @return mixed
     */
    private function getSplitOptions($object = [], $splitOptions = [])
    {
        // We only want one object
        $options['hitsPerPage'] = 1;

        // Add the facet filters
        foreach ($splitOptions['facetFilters'] as $facetFilter) {
            $options['facetFilters'][] = $facetFilter.':'.$object[$facetFilter];
        }

        // Unique fields should be exact matches
        $options['disableTypoToleranceOnAttributes'] = implode(',', $splitOptions['uniqueFields']);

        return $options;
    }
}