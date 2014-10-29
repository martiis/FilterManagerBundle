<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\FilterManagerBundle\Tests\Functional\Response;

use ONGR\ElasticsearchBundle\Test\ElasticsearchTestCase;
use ONGR\FilterManagerBundle\Filters\Widget\Search\MatchSearch;
use ONGR\FilterManagerBundle\Search\FiltersContainer;
use ONGR\FilterManagerBundle\Search\FiltersManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class tests if we provide correct url parameters
 */
class UrlParametersTest extends ElasticsearchTestCase
{
    /**
     * @return array
     */
    protected function getDataArray()
    {
        return [
            'default' => [
                'product' => [
                    [
                        '_id' => 1,
                        'title' => 'Foo product',
                        'description' => 'Very popular product',
                        'color' => 'red',
                    ],
                    [
                        '_id' => 2,
                        'title' => 'Foo cool product',
                        'description' => 'Very popular product',
                        'color' => 'red',
                    ],
                    [
                        '_id' => 3,
                        'title' => 'Another cool product',
                        'description' => 'Very popular product',
                        'color' => 'red',
                    ],
                ]
            ]
        ];
    }

    /**
     * Return any kind of filters manager to test
     *
     * @return FiltersManager
     */
    protected function getFilterManager()
    {
        $container = new FiltersContainer();

        $filter = new MatchSearch();
        $filter->setField('title');
        $filter->setRequestField('q');
        $container->set('title_match', $filter);

        $filter = new MatchSearch();
        $filter->setField('description');
        $filter->setRequestField('d');
        $container->set('description_match', $filter);

        return new FiltersManager($container, $this->getManager()->getRepository('AcmeTestBundle:Product'));
    }

    /**
     * @return array
     */
    public function getTestUrlParametersData()
    {
        $out = [];

        // case #0 not expected parameters
        $out[] = [new Request(['a' => 1, 'b' => 2]), []];

        // case #1 one expected parameters
        $out[] = [new Request(['a' => 1, 'b' => 2, 'd' => 'product']), ['d' => 'product']];

        // case #2 two expected parameters
        $out[] = [
            new Request(['a' => 1, 'b' => 2, 'd' => 'product', 'q' => 'cool']),
            ['q' => 'cool', 'd' => 'product']
        ];

        return $out;
    }

    /**
     * Just test if have correct url parameters
     *
     * @dataProvider getTestUrlParametersData()
     * @param Request $request
     * @param array $expected
     */
    public function testUrlParameters(Request $request, $expected)
    {
        $this->assertEquals($expected, $this->getFilterManager()->execute($request)->getUrlParameters());
    }
}
