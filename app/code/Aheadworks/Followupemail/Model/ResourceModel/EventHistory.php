<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\ResourceModel;

/**
 * EventHistory resource model
 */
class EventHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var bool
     */
    protected $unprocessedOnly = false;

    /**
     * @var string|null
     */
    protected $eventType = null;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * @return $this
     */
    public function setUnprocessedOnly()
    {
        $this->unprocessedOnly = true;
        return $this;
    }

    /**
     * @param string $eventType
     * @return $this
     */
    public function setEventTypeFilter($eventType)
    {
        $this->eventType = $eventType;
        return $this;
    }

    protected function _construct()
    {
        $this->_init('aw_followup_event_history', 'id');
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($this->unprocessedOnly) {
            $select->where('processed=0');
        }
        if ($this->eventType) {
            $select->where('event_type=?', $this->eventType);
        }
        return $select;
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $eventData = $object->getData('event_data');
        if (is_array($eventData)) {
            $object->setData('event_data', serialize($eventData));
        }
        return $this;
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setData('event_data', unserialize($object->getData('event_data')));
        return $this;
    }
}