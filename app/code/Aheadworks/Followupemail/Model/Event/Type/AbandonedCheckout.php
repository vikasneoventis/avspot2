<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Type;

class AbandonedCheckout extends TypeAbstract
{
    /**
     * timeout after which abandoned checkout event may trigger
     */
    const EVENT_TRIGGER_TIMEOUT = 3600;

    /**
     * @var string
     */
    protected $type = 'abandoned_checkout';

    /**
     * @var string
     */
    protected $eventObjectClassName = '\Magento\Quote\Model\Quote';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'quote';

    /**
     * @var string
     */
    protected $referenceDataKey = 'entity_id';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Aheadworks\Followupemail\Model\EventHistoryFactory $eventHistoryFactory
     * @param \Aheadworks\Followupemail\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory
     * @param \Aheadworks\Followupemail\Model\QueueFactory $queueItemFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Aheadworks\Followupemail\Model\EventHistoryFactory $eventHistoryFactory,
        \Aheadworks\Followupemail\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory,
        \Aheadworks\Followupemail\Model\QueueFactory $queueItemFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->dateTime = $dateTime;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($objectManager, $eventHistoryFactory, $queueItemFactory, $eventCollectionFactory, $storeManager);
    }

    /**
     * @param \Aheadworks\Followupemail\Model\EventHistory $historyItem
     */
    public function process(\Aheadworks\Followupemail\Model\EventHistory $historyItem)
    {
        $quote = $this->quoteFactory
            ->create()
            ->setStoreId($historyItem->getEventData('store_id'))
            ->load($historyItem->getReferenceId())

        ;
        if (
            $quote->getIsActive() == 0 ||
            $quote->getItemsCount() == 0
        ) {
            $historyItem->delete();
        } else {
            $triggerAt = $this->dateTime->timestamp($historyItem->getTriggeredAt());
            $now = $this->dateTime->timestamp();
            if ($now - $triggerAt > self::EVENT_TRIGGER_TIMEOUT) {
                $historyItem->setQuote($quote);
                parent::process($historyItem);
            }
        }
    }

    protected function _getEventObject($historyItem)
    {
        return $this->objectManager
            ->create($this->eventObjectClassName)
            ->setStoreId($historyItem->getEventData('store_id'))
            ->load($historyItem->getReferenceId());
    }
}
