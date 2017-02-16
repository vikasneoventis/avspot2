<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;

class SendTest extends \Magento\Backend\App\Action
{
    /**
     * @var \Aheadworks\Followupemail\Model\EventFactory
     */
    protected $eventModelFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Aheadworks\Followupemail\Model\Sender
     */
    protected $sender;

    /**
     * @param Action\Context $context
     * @param \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Aheadworks\Followupemail\Model\Sender $sender
     */
    public function __construct(
        Action\Context $context,
        \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Aheadworks\Followupemail\Model\Sender $sender
    ) {
        $this->eventModelFactory = $eventModelFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sender = $sender;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aheadworks_Followupemail::home_actions_edit');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $this->_getSession()->setFormData($data);
            /* @var $eventModel \Aheadworks\Followupemail\Model\Event */
            $eventModel = $this->eventModelFactory->create();
            $eventModel->setData($data);
            try {
                $this->sender->sendTestEmail($eventModel);
                $this->messageManager->addSuccess(__('Email was successfully sent.'));
            } catch (\Aheadworks\Followupemail\Model\Exception\TestRecipientNotSpecified $e) {
                $configureUrl = $this->getUrl('adminhtml/system_config/edit', ['section' => 'followupemail']);
                $this->messageManager->addError($e->getMessage() . " <a href='{$configureUrl}'>Configure</a>");
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while sending the email.'));
            }
        }
        return $this->resultJsonFactory->create();
    }
}
