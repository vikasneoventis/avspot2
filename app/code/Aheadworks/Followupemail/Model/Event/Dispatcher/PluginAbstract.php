<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Dispatcher;

class PluginAbstract
{
    /**
     * @var \Aheadworks\Followupemail\Model\Event\Dispatcher
     */
    protected $dispatcher;

    /**
     * @param \Aheadworks\Followupemail\Model\Event\Dispatcher $dispatcher
     */
    public function __construct(
        \Aheadworks\Followupemail\Model\Event\Dispatcher $dispatcher
    ) {
        $this->dispatcher = $dispatcher;
    }
}
