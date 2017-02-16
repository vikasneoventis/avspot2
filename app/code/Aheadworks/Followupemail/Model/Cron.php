<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model;

use Magento\Cron\Model\Schedule;

class Cron
{
    /**
     * Event history items to process per one cron run.
     */
    const ITEMS_PER_RUN = 100;

    /**
     * Cron run interval.
     */
    const RUN_INTERVAL = 300;

    /**
     * Cron run daily interval.
     */
    const RUN_DAILY_INTERVAL = 86000;

    /**
     * Location of the "Keep emails for" config param
     */
    const XML_PATH_EXPIRATION_PERIOD = 'followupemail/maillog/keepfor';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Aheadworks\Followupemail\Model\Config
     */
    protected $fueConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Event\Factory
     */
    protected $eventTypeFactory;

    /**
     * @var Sender
     */
    protected $sender;

    /**
     * @var \Aheadworks\Followupemail\Model\ResourceModel\EventHistory\Collection
     */
    protected $eventHistoryCollection;

    /**
     * @var \Aheadworks\Followupemail\Model\ResourceModel\Event\Collection
     */
    protected $eventCollection;

    /**
     * @var \Aheadworks\Followupemail\Model\ResourceModel\Queue\Collection
     */
    protected $queueCollection;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Config $fueConfig
     * @param Event\Factory $eventTypeFactory
     * @param Sender $sender
     * @param ResourceModel\EventHistory\CollectionFactory $eventHistoryCollectionFactory
     * @param ResourceModel\Event\CollectionFactory $eventCollectionFactory
     * @param ResourceModel\Queue\CollectionFactory $queueCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Aheadworks\Followupemail\Model\Config $fueConfig,
        \Aheadworks\Followupemail\Model\Event\Factory $eventTypeFactory,
        \Aheadworks\Followupemail\Model\Sender $sender,
        \Aheadworks\Followupemail\Model\ResourceModel\EventHistory\CollectionFactory $eventHistoryCollectionFactory,
        \Aheadworks\Followupemail\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory,
        \Aheadworks\Followupemail\Model\ResourceModel\Queue\CollectionFactory $queueCollectionFactory
    ) {
        $this->dateTime = $dateTime;
        $this->fueConfig = $fueConfig;
        $this->scopeConfig = $scopeConfig;
        $this->eventTypeFactory = $eventTypeFactory;
        $this->sender = $sender;
        $this->eventHistoryCollection = $eventHistoryCollectionFactory->create();
        $this->eventCollection = $eventCollectionFactory->create();
        $this->queueCollection = $queueCollectionFactory->create();
    }

    public function run()
    {
        if ($this->_isLocked(
            $this->fueConfig->getParam(\Aheadworks\Followupemail\Model\Config::LAST_EXEC_TIME),
            self::RUN_INTERVAL
        )) {
            return;
        }

        $this->_processEventHistoryItems();
        $this->_processQueue();

        $this->_setLastExecTime(\Aheadworks\Followupemail\Model\Config::LAST_EXEC_TIME);
    }

    public function runDaily()
    {
        if ($this->_isLocked(
            \Aheadworks\Followupemail\Model\Config::LAST_EXEC_TIME_DAILY,
            self::RUN_DAILY_INTERVAL
        )) {
            return;
        }

        $this->_clearLog();
        $this->_checkCustomerBirthdays();

        $this->_setLastExecTime(\Aheadworks\Followupemail\Model\Config::LAST_EXEC_TIME_DAILY);
    }

    protected function _isLocked($paramName, $interval)
    {
        $lastExecTime = $this->fueConfig->getParam($paramName);
        $now = $this->dateTime->timestamp();
        return $now < $lastExecTime + $interval;
    }

    protected function _setLastExecTime($paramName)
    {
        $now = $this->dateTime->timestamp();
        $this->fueConfig->setParam($paramName, $now);
    }

    protected function _processEventHistoryItems()
    {
        $eventHistoryItems = $this->eventHistoryCollection;
        $eventHistoryItems
            ->addFieldToFilter('processed', ['eq' => 0])
            ->setPageSize(self::ITEMS_PER_RUN);
        ;
        foreach ($eventHistoryItems as $historyItem) {
            try {
                $eventType = $this->eventTypeFactory->create($historyItem->getEventType());
                if ($eventType) {
                    $eventType->process($historyItem);
                }
            } catch (\Exception $e) {
                // todo: logging exception
            }
        }
    }

    protected function _processQueue()
    {
        $now = date(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT, $this->dateTime->timestamp());
        $queueItems = $this->queueCollection;
        $queueItems
            ->filterByStatus(Queue::STATUS_PENDING)
            ->addFieldToFilter('scheduled_at', ['lteq' => $now]);
        foreach ($queueItems as $queueItem) {
            try {
                $this->sender->sendQueueItem($queueItem);
            } catch (\Exception $e) {
            }
        }
    }

    protected function _clearLog()
    {
        $expirationPeriod = $this->scopeConfig->getValue(self::XML_PATH_EXPIRATION_PERIOD);
        if ($expirationPeriod == 0) {
            return $this;
        }
        $this->queueCollection->deleteExpiredEmails($expirationPeriod);
        $this->eventHistoryCollection->deleteExpiredHistoryItems($expirationPeriod);
    }

    protected function _checkCustomerBirthdays()
    {
        $this->eventCollection
            ->addFieldToFilter('event_type', 'customer_birthday')
            ->addFieldToFilter('active', ['eq' => 1]);
        if (!count($this->eventCollection)) {
            return $this;
        }
        $this->eventTypeFactory
            ->create('customer_birthday')
            ->generateBirthdayEmails($this->eventCollection);
        ;
    }
}
