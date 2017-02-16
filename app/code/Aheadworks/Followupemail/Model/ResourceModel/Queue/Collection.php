<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Followupemail\Model\ResourceModel\Queue;

/**
 * Events collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Followupemail\Model\Queue', 'Aheadworks\Followupemail\Model\ResourceModel\Queue');
        $this->_map['fields']['id'] = 'main_table.id';
        $this->_map['fields']['event_name'] = 'event_table.name';
    }

    public function filterByStatus($status)
    {
        if(is_integer($status)) {
            $this->addFieldToFilter('status', ['eq' => $status]);
        }
        return $this;
    }

    /**
     * Join with 'aw_followup_event' table
     *
     * @return $this
     */
    public function joinEvents()
    {
        $this->getSelect()
            ->join(
                ['event_table' => $this->getTable('aw_followup_event')],
                'main_table.event_id = event_table.id',
                ['event_name' => 'event_table.name']
            )
        ;
        return $this;
    }

    /**
     * Join with 'aw_followup_event_history' table
     *
     * @return $this
     */
    public function joinEventHistory()
    {
        $this->getSelect()
            ->join(
                ['event_history_table' => $this->getTable('aw_followup_event_history')],
                'main_table.event_history_id = event_history_table.id',
                []
            )
        ;
        return $this;
    }

    /**
     * @param int $expirationPeriod days
     */
    public function deleteExpiredEmails($expirationPeriod)
    {
        $expirationDate = date(
            \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT,
            strtotime("-" . $expirationPeriod . " days")
        );
        $this->getConnection()
            ->delete($this->getMainTable(), $this->getConnection()->quoteInto("scheduled_at <= ?", $expirationDate));
    }
}