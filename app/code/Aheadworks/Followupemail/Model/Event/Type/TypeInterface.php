<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Type;

interface TypeInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @param array $data
     * @return mixed
     */
    public function dispatch(array $data = []);

    /**
     * @return string
     */
    public function getReferenceDataKey();

    /**
     * @param \Aheadworks\Followupemail\Model\EventHistory $object
     * @return mixed
     */
    public function process(\Aheadworks\Followupemail\Model\EventHistory $object);
}
