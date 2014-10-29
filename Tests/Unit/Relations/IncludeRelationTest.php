<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\FilterManagerBundle\Tests\Unit\Relations;

use ONGR\ElasticsearchBundle\Test\ElasticsearchTestCase;
use ONGR\FilterManagerBundle\Relations\IncludeRelation;

class IncludeRelationTest extends ElasticsearchTestCase
{
    /**
     * Data provider for testFuzzyQuery()
     *
     * @return array
     */
    public function getTestIncludeRelationData()
    {
        $out = [];

        $out[] = [['a', 'b', 'c'], 'c', true];
        $out[] = [['a', 'b', 'c'], 'd', false];
        $out[] = [[], 'd', false];

        return $out;
    }

    /**
     * Test Include Relation
     *
     * @dataProvider getTestIncludeRelationData
     *
     * @param array     $relations
     * @param string    $name
     * @param bool      $expected
     */
    public function testIncludeRelation($relations, $name, $expected)
    {
        $includeRelation = new IncludeRelation($relations);

        $this->assertEquals($expected, $includeRelation->isRelated($name));
    }
}
