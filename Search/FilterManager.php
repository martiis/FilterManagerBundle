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

use JMS\Serializer\Serializer;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchBundle\Service\Repository;
use ONGR\ElasticsearchBundle\Result\DocumentIterator;
use ONGR\FilterManagerBundle\Event\PreProcessSearchEvent;
use ONGR\FilterManagerBundle\Event\PreSearchEvent;
use ONGR\FilterManagerBundle\Event\SearchResponseEvent;
use ONGR\FilterManagerBundle\Filter\FilterInterface;
use ONGR\FilterManagerBundle\Filter\FilterState;
use ONGR\FilterManagerBundle\Filter\Helper\ViewDataFactoryInterface;
use ONGR\FilterManagerBundle\Filter\ViewData;
use ONGR\FilterManagerBundle\ONGRFilterManagerEvents;
use ONGR\FilterManagerBundle\Relation\ExcludeRelation;
use ONGR\FilterManagerBundle\Relation\FilterIterator;
use ONGR\FilterManagerBundle\Relation\LogicalJoin\AndRelation;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class is entry point for search request execution.
 */
class FilterManager implements FilterManagerInterface
{
    /**
     * @var FilterContainer
     */
    private $container;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param FilterContainer          $container
     * @param Repository               $repository
     * @param EventDispatcherInterface $eventDispatcher
     * @param Serializer $serializer
     */
    public function __construct(
        FilterContainer $container,
        Repository $repository,
        EventDispatcherInterface $eventDispatcher,
        $serializer
    ) {
        $this->container = $container;
        $this->repository = $repository;
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(Request $request, string $resultType = self::RESULT_OBJECT)
    {
        return $this->search($this->container->buildSearchRequest($request), $resultType);
    }

    /**
     * Executes search.
     *
     * @param SearchRequest $request
     * @param string        $resultType
     *
     * @return SearchResponse
     */
    public function search(SearchRequest $request, string $resultType = self::RESULT_OBJECT)
    {
        $this->eventDispatcher->dispatch(ONGRFilterManagerEvents::PRE_SEARCH, new PreSearchEvent($request));

        $search = $this->container->buildSearch($request);

        /** @var FilterInterface $filter */
        foreach ($this->container->all() as $name => $filter) {
            $relatedSearch = new Search();

            if ($filter->isRelated()) {
                // We simply exclude not related filters and current filter itself.
                $relatedFilters = $this->container->getFiltersByRelation(
                    new AndRelation([$filter->getSearchRelation(), new ExcludeRelation([$name])])
                );
                $relatedSearch = $this->container->buildSearch($request, $relatedFilters);
            }

            $this->eventDispatcher->dispatch(
                ONGRFilterManagerEvents::PRE_PROCESS_SEARCH,
                new PreProcessSearchEvent($request->get($name), $relatedSearch)
            );

            $filter->preProcessSearch(
                $search,
                $relatedSearch,
                $request->get($name)
            );
        }

        switch ($resultType) {
            case self::RESULT_RAW:
                $result = $this->repository->findRaw($search);
                break;
            case self::RESULT_ARRAY:
                $result = $this->repository->findArray($search);
                break;
            case self::RESULT_OBJECT:
            default:
                $result = $this->repository->findDocuments($search);
        }

        $this->eventDispatcher->dispatch(ONGRFilterManagerEvents::SEARCH_RESPONSE, new SearchResponseEvent($result));

        return new SearchResponse(
            $this->getFiltersViewData($result, $request),
            $result,
            $this->composeUrlParameters($request),
            $this->serializer
        );
    }

    /**
     * Composes url parameters related to given filter.
     *
     * @param SearchRequest   $request Search request.
     * @param FilterInterface $filter  Filter.
     * @param array           $exclude Additional names of filters to exclude.
     *
     * @return array
     */
    protected function composeUrlParameters(SearchRequest $request, FilterInterface $filter = null, $exclude = [])
    {
        $out = [];

        $and = [];

        if ($filter) {
            $and[] = $filter->getResetRelation();
        }

        if (!empty($exclude)) {
            $and[] = new ExcludeRelation($exclude);
        }

        /** @var FilterState[] $states */
        $states = new FilterIterator(new \IteratorIterator($request), new AndRelation($and));

        foreach ($states as $state) {
            $out = array_merge($out, $state->getUrlParameters());
        }

        return $out;
    }

    /**
     * Creates view data for each filter.
     *
     * @param DocumentIterator $result
     * @param SearchRequest    $request
     *
     * @return ViewData[]
     */
    protected function getFiltersViewData(DocumentIterator $result, SearchRequest $request)
    {
        $out = [];

        /** @var FilterInterface[] $filters */
        $filters = $this->container->all();

        foreach ($filters as $name => $filter) {
            if ($filter instanceof ViewDataFactoryInterface) {
                $viewData = $filter->createViewData();
            } else {
                $viewData = new ViewData();
            }
            $viewData->setName($name);
            $viewData->setUrlParameters($this->composeUrlParameters($request, $filter));
            $viewData->setState($request->get($name));
            $viewData->setTags($filter->getTags());
            $viewData->setResetUrlParameters($this->composeUrlParameters($request, $filter, [$name]));
            $out[$name] = $filter->getViewData($result, $viewData);
        }

        return $out;
    }
}
