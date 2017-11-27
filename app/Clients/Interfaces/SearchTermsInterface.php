<?php namespace JobApis\Collector\Clients\Interfaces;

interface SearchTermsInterface
{
    /**
     * Get terms
     *
     * @param array $options
     *
     * @return array
     */
    public function getTerms($options = []);

    /**
     * Update term by id
     *
     * @param string $id
     * @param array $data
     *
     * @return array
     */
    public function updateTerm($id = null, $data = []);

}