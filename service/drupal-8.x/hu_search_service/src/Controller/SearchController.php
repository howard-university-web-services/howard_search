<?php

namespace Drupal\hu_search_service\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\search_api\Entity\Index;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Defines SearchController class.
 */
class SearchController extends ControllerBase
{
    /**
     * Display the markup.
     *
     * @return array
     *   Return search results array.
	 *
	 * Item interface reference.
	 * https://git.drupalcode.org/project/search_api/-/blob/8.x-1.x/src/Item/ItemInterface.php
     */
    public function search()
    {
        // Get query string parameters.
        $query = Drupal::request()->query->get('query');

        // Execute the search.
        $results = $this->get_index_query_results($query);

        $response = array();
        foreach ($results as $result)
        {
            $resultItem = new ResultItem();
            $resultItem->id = $result->getField('nid')->getValues()[0];
            $resultItem->title = $result->getField('title')->getValues()[0]->getText();
            $resultItem->summary = $result->getField('body')->getValues()[0]->getText();
            $resultItem->url = \Drupal::request()->getSchemeAndHttpHost() . $result->getField('url')->getValues()[0];
            $resultItem->score = $result->getScore();
            $response[] = $resultItem;
        }
		
		return new JsonResponse($response);
    }

    /**
     * Queries a Search API Solr index with the given query text.
     *
     * @param $queryText
     * @return array
     */
    function get_index_query_results($queryText)
    {
        // Load index.
        $config = Drupal::config('hu_search_service.settings');
        $indexId = $config->get('hu_search_service_solr_index');
        $index = Index::load($indexId);
        $query = $index->query();

        // Change the parse mode for the search.
        $parse_mode = Drupal::service('plugin.manager.search_api.parse_mode')
            ->createInstance('direct');
        $parse_mode->setConjunction('OR');
        $query->setParseMode($parse_mode);

        // Set fulltext search keywords and fields.
        $query->keys($queryText);
        $query->setFulltextFields(['title', 'body']);

        // Set additional conditions.
        $query->addCondition('status', 1);

        // Do paging.
        $query->range(0, 1000);

        // Add sorting.
        $query->sort('search_api_relevance', 'DESC');

        // Set one or more tags for the query.
        // @see hook_search_api_query_TAG_alter()
        // @see hook_search_api_results_TAG_alter()
        $query->addTag('custom_search');

        // Execute the search.
        $results = $query->execute();
        return $results->getResultItems();
    }
}
