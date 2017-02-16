<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Followupemail\Model\ResourceModel\Event;

/**
 * Events collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Followupemail\Model\Event', 'Aheadworks\Followupemail\Model\ResourceModel\Event');
    }

    protected function _afterLoad()
    {
        $this->walk('afterLoad');
        parent::_afterLoad();
    }
}