<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\FilterManagerBundle\Search;

use ONGR\ElasticsearchBundle\Result\AbstractResultsIterator;
use ONGR\FilterManagerBundle\Filter\ViewData;

/**
 * This class holds full response of documents and filters data.
 */
class SearchResponse
{
    /**
     * @var AbstractResultsIterator Elasticsearch response object.
     */
    private $result;

    /**
     * @var ViewData[] View data from filters.
     */
    private $filters;

    /**
     * @var array Url Parameters represents current link to list state.
     */
    private $urlParameters;

    /**
     * @param ViewData[]              $filters
     * @param AbstractResultsIterator $result
     * @param array                   $urlParameters
     */
    public function __construct($filters, $result, $urlParameters)
    {
        $this->filters = $filters;
        $this->result = $result;
        $this->urlParameters = $urlParameters;
    }

    /**
     * @return \ONGR\FilterManagerBundle\Filter\ViewData[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return AbstractResultsIterator
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return array
     */
    public function getUrlParameters()
    {
        return $this->urlParameters;
    }
}
