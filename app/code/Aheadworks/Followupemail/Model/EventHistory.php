<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model;

/**
 * EventHistory Model
 * @method array getEventData() getEventData()
 * @method int getReferenceId() getReferenceId()
 * @method bool getProcessed() getProcessed()
 * @method bool getTriggeredAt() getTriggeredAt()
 */
class EventHistory extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Followupemail\Model\ResourceModel\EventHistory');
    }

    /**
     * Load item by reference id and event type.
     * @param $referenceId
     * @param $eventType
     * @return $this
     */
    public function loadByReferenceId($referenceId, $eventType)
    {
        return $this->_setEventTypeFilter($eventType)->load($referenceId, 'reference_id');
    }

    /**
     * @return $this
     */
    public function setUnprocessedOnly()
    {
        $this->getResource()->setUnprocessedOnly();
        return $this;
    }

    /**
     * @param string $eventType
     * @return $this
     */
    protected function _setEventTypeFilter($eventType)
    {
        $this->getResource()->setEventTypeFilter($eventType);
        return $this;
    }
}