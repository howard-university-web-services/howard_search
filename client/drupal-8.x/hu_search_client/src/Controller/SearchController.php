<?php

namespace Drupal\hu_search_client\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\search_api\Entity\Index;

/**
 * Defines SearchController class.
 */
class SearchController extends ControllerBase
{
    /**
     * Display the markup.
     *
     * @return array
     *   Return markup array.
     */
    public function search()
    {
        // Get query string parameters.
        $query = Drupal::request()->query->get('q');
        $searchIndex = Drupal::request()->query->get('i');
        $pageIndex = Drupal::request()->query->get('p');

        // Get configuration settings.
        $config = Drupal::config('hu_search_client.settings');

        $defaultIndex = $config->get('default_search_api_index');
        if (!isset($searchIndex))
        {
            $searchIndex = $defaultIndex;
        }

        // Create response object.
        $response = [
            '#theme' => 'hu_search_client',
            '#query' => $query,
            '#title' => $config->get('search_page_title'),
            '#show_title' => $config->get('show_page_title'),
        ];

        // Execute search.
        if (isset($query) && trim($query) != '')
        {
            // Query default index.
            $categoryIndex = 0;
            $indexResults = $this->get_index_query_results($query, $defaultIndex);
            $response['#categories'][$categoryIndex]['result_count'] = count($indexResults);
            $response['#categories'][$categoryIndex]['name'] = \Drupal::config('system.site')->get('name');
            $response['#categories'][$categoryIndex]['indexes'] = $defaultIndex;

            if ($searchIndex == $defaultIndex)
            {
                $this->get_page_data_for_index($response, $categoryIndex, $searchIndex, $indexResults, $pageIndex, TRUE);
            }

            // Query external indexes.
            $endpointConfigJson = $config->get('howard_search_endpoint_config_json');
            if (isset($endpointConfigJson))
            {
                $endpoints = json_decode($endpointConfigJson);
                foreach ($endpoints as $endpoint)
                {
                    $categoryIndex++;
                    $indexResults = $this->get_api_query_results($query, $endpoint->url);
                    $response['#categories'][$categoryIndex]['result_count'] = count($indexResults);
                    $response['#categories'][$categoryIndex]['name'] = $endpoint->categoryName;
                    $response['#categories'][$categoryIndex]['indexes'] = $endpoint->index;

                    if ($searchIndex == $endpoint->index)
                    {
                        $this->get_page_data_for_index($response, $categoryIndex, $searchIndex, $indexResults, $pageIndex);
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Queries a Search API Slor index with the given query text.
     *
     * @param $queryText
     *    Text to search for.
     * @param $indexId
     *    Index Id to query.
     */
    function get_index_query_results($queryText, $indexId)
    {
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

    /**
     * Queries a Search API Solr index with the given query text.
     *
     * @param $endpoint
     * @param $queryText
     * @return mixed
     */
    function get_api_query_results($queryText, $endpoint)
    {
        $config = Drupal::config('hu_search_client.settings');
        $client = Drupal::httpClient();
        $request = $client->get($endpoint . '/howard/search?query=' . urlencode($queryText), [
            'auth' => [
                $config->get('service_account_username'),
                $config->get('service_account_password')
            ]
        ]);

        return json_decode($request->getBody());
    }

    /**
     * Queries a Search API Solr index with the given query text.
     *
     * @param $response
     * @param $categoryIndex
     * @param $searchIndex
     * @param $indexResults
     * @param $pageIndex
     * @param $isDefault
     *
     * @return mixed
     */
    function get_page_data_for_index(&$response, $categoryIndex, $searchIndex, $indexResults, $pageIndex, $isDefault = FALSE)
    {
        $config = Drupal::config('hu_search_client.settings');
        $resultsPerPageLimit = intval($config->get('results_per_page_limit'));
        $indexResultsCount = count($indexResults);
        $skipResults = 0;

        if (isset($pageIndex) && intval($pageIndex) > 0)
        {
            $skipResults = ($resultsPerPageLimit * intval($pageIndex));
            $response['#display_info']['first_page_result_number'] = $skipResults + 1;
            $response['#display_info']['total_results'] = $indexResultsCount;

            if ($skipResults + $resultsPerPageLimit < $indexResultsCount) {
                $response['#display_info']['last_page_result_number'] = $skipResults + $resultsPerPageLimit;
            }
            else
            {
                $response['#display_info']['last_page_result_number'] = $indexResultsCount;
            }
        }
        else
        {
            if ($resultsPerPageLimit > $indexResultsCount)
            {
                $response['#display_info'] = ['first_page_result_number' => 1, 'last_page_result_number' => $indexResultsCount, 'total_results' => $indexResultsCount];
            }
            else
            {
                $response['#display_info'] = ['first_page_result_number' => 1, 'last_page_result_number' => $resultsPerPageLimit, 'total_results' => $indexResultsCount];
            }
        }

        for ($x = 0; $x < ($indexResultsCount / $resultsPerPageLimit); $x++)
        {
            if (isset($pageIndex) && intval($pageIndex) == $x + 1)
            {
                $response['#page_info'][$x]['is_current_page'] = true;
            }

            $response['#page_info'][$x]['current_indexes'] = $searchIndex;
            $response['#page_info'][$x]['number'] = $x + 1;
        }

        $t = 0;
        $response['#categories'][$categoryIndex]['is_current_result_set'] = true;
        foreach ($indexResults as $result)
        {
            $t++;
            if ($t < $response['#display_info']['first_page_result_number'])
            {
                continue;
            }

            if ($t > $response['#display_info']['last_page_result_number'])
            {
                continue;
            }

            if ($isDefault)
            {
                $response['#results'][$t - 1]['title'] = $result->getField('title')->getValues()[0];
                $response['#results'][$t - 1]['description'] = $result->getField('body')->getValues()[0];
                $response['#results'][$t - 1]['url'] = $result->getField('url')->getValues()[0];
            }
            else
            {
                $response['#results'][$t - 1]['title'] = $result->title;
                $response['#results'][$t - 1]['description'] = $result->summary;
                $response['#results'][$t - 1]['url'] = $result->url;
            }
        }
    }
}
