<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Type;

class OrderStatusChanged extends TypeAbstract
{
    protected $type = 'order_status_changed';

    /**
     * @var string
     */
    protected $eventObjectClassName = '\Magento\Sales\Model\Order';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'order';

    /**
     * @var string
     */
    protected $referenceDataKey = 'entity_id';

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Aheadworks\Followupemail\Model\EventHistoryFactory $eventHistoryFactory
     * @param \Aheadworks\Followupemail\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory
     * @param \Aheadworks\Followupemail\Model\QueueFactory $queueItemFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Aheadworks\Followupemail\Model\EventHistoryFactory $eventHistoryFactory,
        \Aheadworks\Followupemail\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory,
        \Aheadworks\Followupemail\Model\QueueFactory $queueItemFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->quoteFactory = $quoteFactory;
        parent::__construct($objectManager, $eventHistoryFactory, $queueItemFactory, $eventCollectionFactory, $storeManager);
    }

    /**
     * @param \Aheadworks\Followupemail\Model\EventHistory $historyItem
     */
    public function process(\Aheadworks\Followupemail\Model\EventHistory $historyItem)
    {
        $historyItem->setQuote(
            $this->quoteFactory
                ->create()
                ->setStoreId($historyItem->getEventData('store_id'))
                ->load($historyItem->getEventData('quote_id'))
        );
        $historyItem->setOrder(
            $this->objectManager->get($this->eventObjectClassName)->load($historyItem->getReferenceId())
        );
        parent::process($historyItem);
    }

    public function dispatch(array $data = [])
    {
        if (!$this->_validateEventData($data)) {
            return;
        }

        // delete existing abandoned_checkout history item and related queue items
        if (isset($data['quote_id'])) {
            /** @var \Aheadworks\Followupemail\Model\EventHistory $historyItem */
            $historyItem = $this->eventHistoryFactory->create()
                ->loadByReferenceId($data['quote_id'], 'abandoned_checkout')
            ;
            if ($historyItem->getId()) {
                $this->queueItemFactory->create()->deleteItemsByEventHistory($historyItem->getId());
                $historyItem->delete();
            }
        }

        parent::dispatch($data);
    }
}
