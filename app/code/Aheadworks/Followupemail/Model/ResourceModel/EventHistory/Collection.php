<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Followupemail\Model\ResourceModel\EventHistory;

/**
 * EventHistory collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Aheadworks\Followupemail\Model\EventHistory', 'Aheadworks\Followupemail\Model\ResourceModel\EventHistory');
    }

    protected function _afterLoad()
    {
        $this->walk('afterLoad');
        parent::_afterLoad();
    }

    /**
     * @param int $expirationPeriod days
     */
    public function deleteExpiredHistoryItems($expirationPeriod)
    {
        //TODO: make more intelligent deletion algorythm, using queue.
        $expirationPeriod = $expirationPeriod + 30;
        $expirationDate = date(
            \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT,
            strtotime("-" . $expirationPeriod . " days")
        );
        $this->getConnection()
            ->delete($this->getMainTable(), $this->getConnection()->quoteInto("triggered_at <= ?", $expirationDate));
    }
}