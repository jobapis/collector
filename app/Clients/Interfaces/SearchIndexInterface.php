<?php namespace JobApis\Collector\Clients\Interfaces;

interface SearchIndexInterface
{
    /**
     * Add objects to the search index
     *
     * @param array $objects
     *
     * @return mixed
     */
    public function addObjects($objects = []);

    /**
     * Adds or updates each object depending on whether it's been added before
     *
     * @param array $objects
     * @param array $options
     *
     * @return mixed
     */
    public function addOrUpdateObjects($objects = [], $options = []);

    /**
     * Delete objects from the search index
     *
     * @param array $objects
     *
     * @return mixed
     */
    public function deleteObjects($objects = []);

    /**
     * Search for a specific term in the index
     *
     * @param string $query
     * @param array $options
     *
     * @return array
     */
    public function search(string $query, $options = []);

    /**
     * Update objects in the search index
     *
     * @param array $objects
     *
     * @return mixed
     */
    public function updateObjects($objects = []);
}