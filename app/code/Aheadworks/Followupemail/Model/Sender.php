<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model;

use Magento\TestFramework\Inspection\Exception;

class Sender
{
    /**
     * Location of the "Sender" config param
     */
    const XML_PATH_SENDER_IDENTITY = 'followupemail/general/sender';

    /**
     * Location of the "Enable test mode" config param
     */
    const XML_PATH_ENABLE_TESTMODE = 'followupemail/general/enabletestmode';

    /**
     * Location of the "Test email recepient" config param
     */
    const XML_PATH_TEST_EMAIL = 'followupemail/general/testemail';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var bool
     */
    protected $isTestMode;

    /**
     * @var string
     */
    protected $prefixSubject = '';

    /**
     * @var Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var Event\Factory
     */
    protected $eventFactory;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @param Template\TransportBuilder $transportBuilder
     * @param Event\Factory $eventFactory
     * @param QueueFactory $queueFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Template\TransportBuilder $transportBuilder,
        \Aheadworks\Followupemail\Model\Event\Factory $eventFactory,
        \Aheadworks\Followupemail\Model\QueueFactory $queueFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->eventFactory = $eventFactory;
        $this->queueFactory = $queueFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Event $event
     * @throws Exception\TestRecipientNotSpecified
     */
    public function sendTestEmail(Event $event)
    {
        $recipientEmail = $this->_getTestEmailAddress(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        if (!$recipientEmail) {
            throw new \Aheadworks\Followupemail\Model\Exception\TestRecipientNotSpecified(
                __('Unable to send test email. Test Email Recipient is not specified.')
            );
        }
        $emailData = $this->eventFactory
            ->create($event->getEventType())
            ->getTestEmailData()
        ;
        $this->prefixSubject = '[TEST EMAIL] ';
        $this->sendEmail($emailData, $event, $recipientEmail);
        $this->queueFactory->create()
            ->setSavedSubject($this->transportBuilder->getMessageSubject())
            ->setSavedContent($this->transportBuilder->getMessageContent())
            ->setSendTime()
            ->addTestEmail($event, $emailData, $recipientEmail)
        ;
    }

    public function sendQueueItem(Queue $queueItem)
    {
        try {
            $storeId = $queueItem->getStoreId();
            $recipientEmail = $this->_isTestMode($storeId) ? $this->_getTestEmailAddress($storeId) : $queueItem->getRecipientEmail();
            if ($queueItem->getSavedContent()) {
                $this->sendSavedEmail($queueItem, $recipientEmail);
                $queueItem
                    ->setStatus(Queue::STATUS_SENT)
                    ->setSendTime()
                    ->save();
            }
            else {
                $emailData = $this->eventFactory
                    ->create($queueItem->getEventType())
                    ->getEmailData($queueItem);
                $this->sendEmail($emailData, $queueItem->getEvent(), $recipientEmail);
                $queueItem
                    ->setSavedSubject($this->transportBuilder->getMessageSubject())
                    ->setSavedContent($this->transportBuilder->getMessageContent())
                    ->setStatus(Queue::STATUS_SENT)
                    ->setSendTime()
                ;
                if ($this->_isTestMode($storeId)) {
                    $queueItem->setRecipientEmail($recipientEmail);
                }
                $queueItem->save();
            }
        } catch (\Magento\Framework\Exception\MailException $e) {
            $queueItem->setStatus(Queue::STATUS_FAILED)->save()
            ;
        }
        return $this;
    }

    public function sendEmail($emailData, $event, $recipientEmail)
    {
        $this->transportBuilder
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $emailData['store_id']
            ])
            ->setTemplateVars($emailData)
            ->setTemplateData([
                'template_subject' => $this->prefixSubject . $event->getSubject(),
                'template_text' => $event->getContent()
            ])
            ->setFrom($this->scopeConfig->getValue(
                self::XML_PATH_SENDER_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $emailData['store_id']
            ))
            ->addTo($recipientEmail, $emailData['customer_name'])
        ;
        $this->prefixSubject = '';

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    public function sendSavedEmail(Queue $queueItem, $recipientEmail)
    {
        $storeId = $queueItem->getStoreId();
        $this->transportBuilder
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ])
            ->setTemplateVars(array())
            ->setTemplateData([
                'template_subject' => $queueItem->getSavedSubject(),
                'template_text' => $queueItem->getSavedContent()
            ])
            ->setFrom($this->scopeConfig->getValue(
                self::XML_PATH_SENDER_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            ))
            ->addTo($recipientEmail, $queueItem->getRecipientName())
        ;
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    protected function _getTestEmailAddress($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TEST_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Checks if need to use test email address for sending
     *
     * @param $storeId
     * @return bool
     */
    protected function _isTestMode($storeId)
    {
        if (is_null($this->isTestMode)) {
            $this->isTestMode = (bool)$this->scopeConfig->getValue(
                self::XML_PATH_ENABLE_TESTMODE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $this->isTestMode;
    }
}