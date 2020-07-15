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
 * @param array $op
 * @param array $args
 *
 * @return boolean
 *
 */
function api_query_result_access($op, $args)
{
    return user_access($args[1]);
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
