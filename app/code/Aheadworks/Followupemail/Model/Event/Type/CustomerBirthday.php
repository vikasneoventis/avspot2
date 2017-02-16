<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Type;

class CustomerBirthday extends TypeAbstract
{
    /**
     * @var string
     */
    protected $type = 'customer_customer_birthday';

    /**
     * @var string
     */
    protected $eventObjectClassName = '\Magento\Customer\Model\Customer';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'customer';

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var string
     */
    protected $referenceDataKey = 'entity_id';

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Aheadworks\Followupemail\Model\EventHistoryFactory $eventHistoryFactory
     * @param \Aheadworks\Followupemail\Model\QueueFactory $queueItemFactory
     * @param \Aheadworks\Followupemail\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Aheadworks\Followupemail\Model\EventHistoryFactory $eventHistoryFactory,
        \Aheadworks\Followupemail\Model\QueueFactory $queueItemFactory,
        \Aheadworks\Followupemail\Model\ResourceModel\Event\CollectionFactory $eventCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        parent::__construct($objectManager, $eventHistoryFactory, $queueItemFactory, $eventCollectionFactory, $storeManager);
    }



    public function generateBirthdayEmails(\Aheadworks\Followupemail\Model\ResourceModel\Event\Collection $bithdayEvents)
    {
        $queueItem = $this->queueItemFactory->create();

        foreach ($bithdayEvents as $event) {
            $birthdayDate = date(
                'm-d',
                strtotime("+" . $event->getEmailSendDays() . " days")
            );
            $customers = $this->customerCollectionFactory->create();

            $customers->getSelect()
                ->where('DATE_FORMAT(dob, "%m-%d")=?', $birthdayDate)
            ;
            foreach ($customers as $customer) {
                $queueItem->addBirthdayEmail($event, $customer);
            }
        }
    }

    /**
     * Create objects for template variables
     *
     * @param \Aheadworks\Followupemail\Model\EventHistory $historyItem
     * @return array
     */
    public function getEmailData(\Aheadworks\Followupemail\Model\Queue $queueItem)
    {
        $emailData = array();
        $store = $this->storeManager->getStore($queueItem->getStoreId());
        if ($customerEmail = $queueItem->getRecipientEmail()) {
            $customer = $this->objectManager
                ->create('\Magento\Customer\Model\Customer')
                ->setWebsiteId($store->getWebsiteId())
                ->loadByEmail($customerEmail);
            if ($customer->getId()) {
                $emailData['customer'] = $customer;
            }
        }
        if (isset($emailData['customer'])) {
            $emailData['store'] = $store;
            $emailData = array_merge($emailData, [
                'email'  => $emailData['customer']->getEmail(),
                'store_id'  => $emailData['customer']->getStoreId(),
                'customer_group_id'  => $emailData['customer']->getGroupId(),
                'customer_name' => $emailData['customer']->getName()
            ]);
        }
        return $emailData;
    }
}
