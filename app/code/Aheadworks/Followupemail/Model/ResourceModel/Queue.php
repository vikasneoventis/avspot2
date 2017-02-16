<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Followupemail\Model\ResourceModel;

/**
 * Queue resource model
 */
class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
    }

    protected function _construct()
    {
        $this->_init('aw_followup_queue', 'id');
    }

    public function deleteItemsByEventHistory($historyItemId)
    {
        $writeAdapter = $this->getConnection();
        $conditions = sprintf(
            'event_history_id=%s AND status<>%s',
            $writeAdapter->quote($historyItemId),
            $writeAdapter->quote(\Aheadworks\Followupemail\Model\Queue::STATUS_SENT)
        );
        $writeAdapter->delete($this->getMainTable(), $conditions);
    }
}
