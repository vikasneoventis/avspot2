<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml;

class Preview extends \Magento\Backend\Block\Template
{
    /**
     * @var \Aheadworks\Followupemail\Model\Event|\Aheadworks\Followupemail\Model\Queue
     */
    protected $previewModel = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Aheadworks\Followupemail\Model\QueueFactory
     */
    protected $queueFactory;

    /**
     * @var \Aheadworks\Followupemail\Model\EventFactory
     */
    protected $eventModelFactory;

    /**
     * @var \Aheadworks\Followupemail\Model\Event\Factory
     */
    protected $eventFactory;

    /**
     * @var \Aheadworks\Followupemail\Model\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Mail\Template\SenderResolverInterface
     */
    protected $senderResolver;

    /**
     * @var string|null
     */
    protected $messageSubject = null;

    /**
     * @var string|null
     */
    protected $messageBody = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Aheadworks\Followupemail\Model\Template\TransportBuilder $transportBuilder
     * @param \Aheadworks\Followupemail\Model\QueueFactory $queueFactory
     * @param \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory
     * @param \Aheadworks\Followupemail\Model\Event\Factory $eventFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Aheadworks\Followupemail\Model\Template\TransportBuilder $transportBuilder,
        \Aheadworks\Followupemail\Model\QueueFactory $queueFactory,
        \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory,
        \Aheadworks\Followupemail\Model\Event\Factory $eventFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver,
        array $data = []
    ) {
        $this->queueFactory = $queueFactory;
        $this->eventModelFactory = $eventModelFactory;
        $this->transportBuilder = $transportBuilder;
        $this->eventFactory = $eventFactory;
        $this->coreRegistry = $registry;
        $this->senderResolver = $senderResolver;
        parent::__construct($context, $data);
    }

    protected function _preview()
    {
        if ($this->_getPreviewType() == 'queue') {
            /** @var \Aheadworks\Followupemail\Model\Queue $queueItem */
            $queueItem = $this->_getPreviewModel();
            if ($queueItem->getSavedContent()) {
                $this->messageSubject = $queueItem->getSavedSubject();
                $this->messageBody = $queueItem->getSavedContent();
            }
            else {
                $emailData = $this->eventFactory
                    ->create($queueItem->getEventType())
                    ->getEmailData($queueItem);
                $this->transportBuilder
                    ->setTemplateOptions([
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $emailData['store_id']
                    ])
                    ->setTemplateVars($emailData)
                    ->setTemplateData([
                        'template_subject' => $queueItem->getEvent()->getSubject(),
                        'template_text' => $queueItem->getEvent()->getContent()
                    ])
                    ->addTo($queueItem->getRecipientEmail(), $emailData['customer_name'])
                ;
                $this->transportBuilder->prepareForPreview();
                $this->messageSubject = $this->transportBuilder->getMessageSubject();
                $this->messageBody = $this->transportBuilder->getMessageContent();
            }
        } else {
            $eventModel = $this->_getPreviewModel();
            $emailData = $this->eventFactory
                ->create($eventModel->getEventType())
                ->getTestEmailData();

            $this->transportBuilder
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $emailData['store_id']
                ])
                ->setTemplateVars($emailData)
                ->setTemplateData([
                    'template_subject' => $eventModel->getSubject(),
                    'template_text' => $eventModel->getContent()
                ])
            ;
            $this->transportBuilder->prepareForPreview();
            $this->messageSubject = $this->transportBuilder->getMessageSubject();
            $this->messageBody = $this->transportBuilder->getMessageContent();
        }
    }

    protected function _getPreviewModel()
    {
        if ($this->previewModel === null) {
            $this->previewModel = $this->coreRegistry->registry('preview_model');
        }
        return $this->previewModel;
    }

    protected function _getPreviewType()
    {
        return $this->_getPreviewModel() instanceof \Aheadworks\Followupemail\Model\Queue ? 'queue' : 'event';
    }

    protected function _getPreviewStoreId()
    {
        if ($this->_getPreviewType() == 'queue') {
            $storeId = $this->_getPreviewModel()->getStoreId();
        } else {
            $stores = $this->_getPreviewModel()->getStores();
            $storeId = $stores ? array_shift($stores) : \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        return $storeId;
    }

    protected function _getSenderData()
    {
        $from = $this->_scopeConfig->getValue(
            \Aheadworks\Followupemail\Model\Sender::XML_PATH_SENDER_IDENTITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_getPreviewStoreId()
        );
        return $this->senderResolver->resolve($from);
    }

    /**
     * Get sender name
     *
     * @return mixed
     */
    public function getSenderName()
    {
        $data = $this->_getSenderData();
        return $data['name'];
    }

    /**
     * Get sender email
     *
     * @return mixed
     */
    public function getSenderEmail()
    {
        $data = $this->_getSenderData();
        return $data['email'];
    }

    /**
     * @return string
     */
    public function getRecipientName()
    {
        if ($this->_getPreviewType() == 'queue') {
            return $this->_getPreviewModel()->getRecipientName();
        } else {
            // todo: make for event object
            return '';
        }
    }

    /**
     * Get recipient email
     *
     * @return string
     */
    public function getRecipientEmail()
    {
        if ($this->_getPreviewType() == 'queue') {
            return $this->_getPreviewModel()->getRecipientEmail();
        } else {
            return $this->_scopeConfig->getValue(
                \Aheadworks\Followupemail\Model\Sender::XML_PATH_TEST_EMAIL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_getPreviewStoreId()
            );
        }
    }

    /**
     * Get email body
     *
     * @return string
     */
    public function getMessageContent()
    {
        if ($this->messageBody == null) {
            $this->_preview();
        }
        return $this->messageBody;
    }

    /**
     * Get email subject
     *
     * @return null|string
     */
    public function getMessageSubject()
    {
        if ($this->messageSubject == null) {
            $this->_preview();
        }
        return $this->messageSubject;
    }
}
