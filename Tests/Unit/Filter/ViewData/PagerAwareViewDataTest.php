<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\FilterManagerBundle\Tests\Unit\Filter\ViewData;

use ONGR\FilterManagerBundle\Filter\ViewData\PagerAwareViewData;

class PagerAwareViewDataTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckPageNavigation()
    {
        $pagerData = new PagerAwareViewData();
        $pagerData->setState($this->createMock('ONGR\FilterManagerBundle\Filter\FilterState'));
        $pagerData->setData(100, 1, 12, 5);

        $this->assertTrue($pagerData->isFirstPage());
        $this->assertFalse($pagerData->isLastPage());
        $this->assertEquals(2, $pagerData->getNextPage());

        $pagerData->setData(100, 9, 12, 5);
        $this->assertTrue($pagerData->isLastPage());
        $this->assertFalse($pagerData->isFirstPage());
        $this->assertEquals(1, $pagerData->getFirstPage());
    }

    public function testGetPages()
    {
        $pagerData = new PagerAwareViewData();
        $pagerData->setState($this->createMock('ONGR\FilterManagerBundle\Filter\FilterState'));
        $pagerData->setData(100, 1, 12, 5);

        $this->assertEquals(range(2, 5, 1), $pagerData->getPages());

        $pagerData->setData(100, 5, 12, 5);
        $this->assertEquals(range(4, 7, 1), $pagerData->getPages());

        $pagerData->setData(100, 9, 12, 5);
        $this->assertEquals(range(6, 9, 1), $pagerData->getPages());
    }
}
