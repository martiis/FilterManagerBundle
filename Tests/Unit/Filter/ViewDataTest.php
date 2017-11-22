<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\FilterManagerBundle\Tests\Unit\Filter;

use ONGR\FilterManagerBundle\Filter\ViewData;

class ViewDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Tags getter and setter
     */
    public function testTagGetterAndSetter()
    {
        $viewData = new ViewData();
        $this->assertFalse($viewData->hasTag('tags'));
        $viewData->setTags(['tags']);
        $this->assertTrue($viewData->hasTag('tags'));
        $this->assertEquals(['tags'], $viewData->getTags());
    }
}
