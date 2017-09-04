<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\FilterManagerBundle\Event;

use ONGR\ElasticsearchBundle\Result\AbstractResultsIterator;
use Symfony\Component\EventDispatcher\Event;

class SearchResponseEvent extends Event
{
    /**
     * @var AbstractResultsIterator
     */
    private $documentIterator;

    /**
     * Constructor
     *
     * @param AbstractResultsIterator $documentIterator
     */
    public function __construct(AbstractResultsIterator $documentIterator)
    {
        $this->documentIterator = $documentIterator;
    }

    /**
     * Returns document iterator
     *
     * @return AbstractResultsIterator
     */
    public function getDocumentIterator()
    {
        return $this->documentIterator;
    }
}
