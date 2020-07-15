<?php

namespace Drupal\hu_search_service\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\search_api\Entity\Index;

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
