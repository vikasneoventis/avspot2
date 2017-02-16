<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Type;

abstract class TypeAbstract implements TypeInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $referenceDataKey = 'entity_id';

    /**
     * @var string
     */
    protected $eventObjectClassName;

    /**
     * @var string
     */
    protected $eventObjectVariableName;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Aheadworks\Followupemail\Model\EventHistoryFactory
     */
    protected $eventHistoryFactory;

    /**
     * @var \Aheadworks\Followupemail\Model\QueueFactory
     */
    protected $queueItemFactory;

    /**
     * @var \Aheadworks\Followupemail\Model\ResourceModel\Event\Collection
     */
    protected $eventCollection;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Aheadworks\Followupemail\Model\EventHistoryFactory $eventHistoryFactory
     * @param \Aheadworks\Followupemail\Model\QueueFactory $queueItemFactory
     * @param \Aheadworks\Followupemail\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Aheadworks\Followupemail\Model\EventHistoryFactory $eventHistoryFactory,
        \Aheadworks\Followupemail\Model\QueueFactory $queueItemFactory,
        \Aheadworks\Followupemail\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->eventHistoryFactory = $eventHistoryFactory;
        $this->queueItemFactory = $queueItemFactory;
        $this->eventCollection = $eventCollectionFactory->create()
            ->addFieldToFilter('event_type', ['eq' => $this->type])
            ->addFieldToFilter('active', ['eq' => 1])
        ;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getReferenceDataKey()
    {
        return $this->referenceDataKey;
    }

    /**
     * Create objects for template variables
     *
     * @param \Aheadworks\Followupemail\Model\EventHistory $historyItem
     * @return array
     * @throws Exception
     */
    public function getEmailData(\Aheadworks\Followupemail\Model\Queue $queueItem)
    {
        $historyItem = $queueItem->getEventHistoryItem();
        $emailData = $historyItem->getEventData();
        // Create event object instance
        $eventObject = $this->_getEventObject($historyItem);
        if ($eventObject->getId()) {
            $emailData[$this->eventObjectVariableName] = $eventObject;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__("Event object is missing"));
        }
        // Create customer instance
        if ($historyItem->getEventData('customer_id')) {
            $customer = $this->objectManager
                ->create('\Magento\Customer\Model\Customer')
                ->load($historyItem->getEventData('customer_id'));
            if ($customer->getId()) {
                $emailData['customer'] = $customer;
            }
        }
        // Create store instance
        if (isset($emailData['customer'])) {
            $emailData['store'] = $this->storeManager->getStore($emailData['customer']->getStoreId());
        }
        return $emailData;
    }

    protected function _getEventObject($historyItem)
    {
        return $this->objectManager
            ->create($this->eventObjectClassName)
            ->load($historyItem->getReferenceId());
    }

    //generate objects and event data for test email
    public function getTestEmailData()
    {
        $emailData = [];
        // Create eventObject instance
        $collection = $this->objectManager
            ->create($this->eventObjectClassName)->getCollection();
        $collection->getSelect()
            ->order(new \Zend_Db_Expr('RAND()'))
            ->limit(1)
        ;
        $eventObject = $collection->getFirstItem();
        $emailData[$this->eventObjectVariableName] = $eventObject;
        // Create customer instance
        if ($this->eventObjectVariableName != 'customer') {
            if ($customerId = $eventObject->getData('customer_id')) {
                $customer = $this->objectManager
                    ->create('\Magento\Customer\Model\Customer')
                    ->load($customerId);
            } else {
                $customerCollection = $this->objectManager
                    ->create('\Magento\Customer\Model\Customer')->getCollection();
                $customerCollection->getSelect()
                    ->order(new \Zend_Db_Expr('RAND()'))
                    ->limit(1)
                ;
                $customer = $customerCollection->getFirstItem();
            }
            $emailData['customer'] = $customer;
        }
        // Create store instance
        $emailData['store'] = $this->storeManager->getStore($emailData['customer']->getStoreId());
        // Add required event data
        $customerData = [
            'email'  => $emailData['customer']->getEmail(),
            'store_id'  => $emailData['customer']->getStoreId(),
            'customer_group_id'  => $emailData['customer']->getGroupId(),
            'customer_firstname' => $emailData['customer']->getFirstname(),
            'customer_name' => $emailData['customer']->getName()
        ];
        return array_merge($emailData, $eventObject->getData(), $customerData);
    }

    public function dispatch(array $data = [])
    {
        if (!$this->_validateEventData($data)) {
            return;
        }
        /** @var \Aheadworks\Followupemail\Model\EventHistory $historyItem */
        $historyItem = $this->eventHistoryFactory->create()
            ->loadByReferenceId($data[$this->getReferenceDataKey()], $this->type)
        ;
        // delete existing history item and related queue items
        if ($historyItem->getProcessed()) {
            $this->queueItemFactory->create()->deleteItemsByEventHistory($historyItem->getId());
            $historyItem->delete();
            $historyItem = $this->eventHistoryFactory->create();
        }

        $historyItem->addData([
            'reference_id' => $data[$this->getReferenceDataKey()],
            'event_type' => $this->getType(),
            'event_data' => $this->serializeEventData($data)
        ]);
        $historyItem->save();
    }

    /**
     * Serialize of event data
     *
     * @param array $data
     * @return string
     */
    protected function serializeEventData(array $data)
    {
        foreach ($data as $key => $value) {
            if ((is_array($value) || is_object($value))) {
                unset($data[$key]);
            }

            if (isset($data[$key]) && preg_match("/\r\n|\r|\n/", $value)) {
                $data[$key] = preg_replace("/\r\n|\r|\n/", "", $value);
            }
        }
        return serialize($data);
    }

    /**
     * Validation of event data
     *
     * @param array $data
     * @return bool
     */
    protected function _validateEventData(array $data)
    {
        $dataKeysRequired = ['email', 'store_id', 'customer_group_id', 'customer_name'];
        foreach ($dataKeysRequired as $dataKey) {
            if (!array_key_exists($dataKey, $data)) {
                return false;
            }
        }
        return true;
    }

    public function process(\Aheadworks\Followupemail\Model\EventHistory $historyItem)
    {
        /** @var \Aheadworks\Followupemail\Model\Queue $queueItem */
        $queueItem = $this->queueItemFactory->create();

        $hasQueueItems = false;
        foreach ($this->eventCollection as $event) {
            /** @var $event \Aheadworks\Followupemail\Model\Event */
            if ($event->validate($historyItem)) {

                $queueItem->add($event, $historyItem);
                $hasQueueItems = true;
            }
        }
        if ($hasQueueItems) {
            $historyItem->setProcessed(true)->save();
        } else {
            $historyItem->delete();
        }
    }
}
