<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Aheadworks\Followupemail\Model\EventFactory
     */
    protected $eventModelFactory;

    /**
     * @param Action\Context $context
     * @param \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory
     */
    public function __construct(
        Action\Context $context,
        \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory
    ) {
        $this->eventModelFactory = $eventModelFactory;
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

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /* @var $eventModel \Aheadworks\Followupemail\Model\Event */
            $eventModel = $this->eventModelFactory->create();

            $id = $this->getRequest()->getParam('id');
            $back = $this->getRequest()->getParam('back');
            if ($id && $back != 'new') {
                $eventModel->load($id);
            }
            if ($back == 'new') {
                unset($data['id']);
            }
            $eventModel->setData($data);
            try {
                $eventModel->save();
                $this->messageManager->addSuccess(__('Email was successfully saved'));
                if ($back == 'edit') {
                    return $resultRedirect->setPath('*/*/' . $back, ['id' => $eventModel->getId(), '_current' => true]);
                }
                $this->_getSession()->setFormData(false);
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the email.'));
            }
            $data['id'] = $id;
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
