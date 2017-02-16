<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event;

/**
 * Class Dispatcher
 * @package Aheadworks\Followupemail\Model\Event
 */
class Dispatcher
{
    /**
     * @var Factory
     */
    protected $eventFactory;

    /**
     * @param Factory $eventFactory
     */
    public function __construct(
        \Aheadworks\Followupemail\Model\Event\Factory $eventFactory
    ) {
        $this->eventFactory = $eventFactory;
    }

    /**
     * @param string $type
     * @param array $data
     */
    public function dispatch($type, $data)
    {
        try {
            $instance = $this->eventFactory->create($type);
            if ($instance) {
                $instance->dispatch($data);
            }
        } catch (\Exception $e) {
            // todo: logging exception
        }
    }
}
