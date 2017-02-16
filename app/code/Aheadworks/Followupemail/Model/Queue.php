<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model;

/**
 * Queue model
 *
 * @method string getEventType() getEventType()
 * @method Queue setStatus() setStatus()
 * @method Queue setRecipientEmail() setRecipientEmail()
 * @method Queue setSavedSubject() setSavedSubject()
 * @method Queue setSavedContent() setSavedContent()
 * @method Queue getSavedSubject() getSavedSubject()
 * @method Queue getSavedContent() getSavedContent()
 */
class Queue extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_PENDING = 1;
    const STATUS_SENT = 2;
    const STATUS_FAILED = 3;
    const STATUS_CANCELLED = 4;

    /**
     * @var \Aheadworks\Followupemail\Model\EventHistory
     */
    protected $eventHistoryItem;

    /**
     * @var \Aheadworks\Followupemail\Model\Event
     */
    protected $event;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localDate;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localDate,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Followupemail\Model\EventFactory $eventFactory,
        \Aheadworks\Followupemail\Model\EventHistoryFactory $eventHistoryItemFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->dateTime = $dateTime;
        $this->localDate = $localDate;
        $this->event = $eventFactory->create();
        $this->eventHistoryItem = $eventHistoryItemFactory->create();
    }

    protected function _construct()
    {
        $this->_init('Aheadworks\Followupemail\Model\ResourceModel\Queue');
    }

    public function add(Event $event, EventHistory $historyItem)
    {
        $this
            ->setData(array(
                'event_type'  =>  $event->getEventType(),
                'event_id'  =>  $event->getId(),
                'status'  =>  self::STATUS_PENDING,
                'scheduled_at'  =>  $event->getSendTime($historyItem->getTriggeredAt()),
                'store_id' => $historyItem->getEventData('store_id'),
                'recipient_name' => $historyItem->getEventData('customer_name'),
                'recipient_email' => $historyItem->getEventData('email'),
                'event_history_id'  =>  $historyItem->getId()
            ))
            ->save()
        ;
    }

    public function addBirthdayEmail(Event $event, \Magento\Customer\Model\Customer $customer)
    {
        $this
            ->setData(array(
                'event_type'  =>  $event->getEventType(),
                'event_id'  =>  $event->getId(),
                'status'  =>  self::STATUS_PENDING,
                'scheduled_at'  =>  $this->dateTime->timestamp(),
                'store_id' => $customer->getStoreId(),
                'recipient_name' => $customer->getName(),
                'recipient_email' => $customer->getEmail(),
                'event_history_id'  =>  0
            ))
            ->save()
        ;
    }

    public function addTestEmail(Event $event, array $emailData, $recipientEmail)
    {
        $this
            ->addData(array(
                'event_type'  =>  $event->getEventType(),
                'event_id'  =>  $event->getId(),
                'status'  =>  self::STATUS_SENT,
                'scheduled_at'  =>  $this->dateTime->timestamp(),
                'store_id' => $emailData['store_id'],
                'recipient_name' => $emailData['customer_name'],
                'recipient_email' => $recipientEmail,
                'event_history_id'  =>  0
            ))
            ->save()
        ;
        return $this;
    }

    public function setSendTime($timestamp = null)
    {
        if(!$timestamp) {
            $timestamp = $this->dateTime->timestamp();
        }
        $this->setData('sent_at', $timestamp);
        return $this;
    }

    /**
     * @return EventHistory
     */
    public function getEventHistoryItem()
    {
        if (!$this->eventHistoryItem->getId() && $this->getEventHistoryId()) {
            $this->eventHistoryItem->load($this->getEventHistoryId());
        }
        return $this->eventHistoryItem;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        if (!$this->event->getId() && $this->getEventId()) {
            $this->event->load($this->getEventId());
        }
        return $this->event;
    }

    public function deleteItemsByEventHistory($historyItemId)
    {
        $this->getResource()->deleteItemsByEventHistory($historyItemId);
    }

    public function deleteLinkedHistoryItem()
    {
        $this->getEventHistoryItem()->delete();
        return $this;
    }

    /**
     * @return bool
     */
    public function canCancel()
    {
        return !in_array($this->getStatus(), [
            self::STATUS_CANCELLED,
            self::STATUS_FAILED,
            self::STATUS_SENT
        ]);
    }
}