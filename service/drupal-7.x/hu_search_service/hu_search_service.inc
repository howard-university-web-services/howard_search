<?php

/**
 * Callback for getting search results.
 *
 * @param string $query
 *
 * @return array
 */
function api_get_query_results($query)
{
    return get_index_query_results($query);
}

/**
 * Permissions callback method for additional extensibility.
 *
 * @return boolean
 */
function api_query_result_access()
{
    return user_access('Access Howard University Search');
}

/**
 * Represents a result item that will be returned by the REST API.
 */
class ResultItem
{
    /**
     * Drupal node Id.
     */
    public $id;

    /**
     * Node title.
     */
    public $title;

    /**
     * Node summary text.
     */
    public $summary;

    /**
     * Node URL.
     */
    public $url;

    /**
     * Search applicability score.
     */
    public $score;
}
