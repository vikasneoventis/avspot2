<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Cancel extends \Magento\Backend\App\Action
{
    /**
     * @var \Aheadworks\Followupemail\Model\QueueFactory
     */
    protected $queueFactory;

    /**
     * @param Context $context
     * @param \Aheadworks\Followupemail\Model\QueueFactory $queueFactory
     */
    public function __construct(
        Context $context,
        \Aheadworks\Followupemail\Model\QueueFactory $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aheadworks_Followupemail::mail_log_actions');
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $queueId = (array) $this->getRequest()->getParam('id');
        if ($queueId) {
            /** @var $queueModel \Aheadworks\Followupemail\Model\Queue */
            $queueModel = $this->queueFactory->create()->load($queueId);
            if ($queueModel->getId() && $queueModel->canCancel()) {
                try {
                    $queueModel
                        ->setStatus(\Aheadworks\Followupemail\Model\Queue::STATUS_CANCELLED)
                        ->save()
                    ;
                    $this->messageManager->addSuccess(__('Email was successfully cancelled.'));
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while cancelling the email.'));
                }
            } else {
                $this->messageManager->addWarning(__('This email cannot be cancelled.'));
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('followupemail_admin/*/');
    }
}
