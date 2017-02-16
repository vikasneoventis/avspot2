<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Controller\Adminhtml\Event;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Aheadworks\Followupemail\Model\EventFactory
     */
    protected $eventModelFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory
    ) {
        parent::__construct($context);
        $this->layoutFactory = $layoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eventModelFactory = $eventModelFactory;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aheadworks_Followupemail::home_actions_edit');
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        /* @var $eventModel \Aheadworks\Followupemail\Model\Event */
        $eventModel = $this->eventModelFactory->create();
        if ($id) {
            $eventModel->load($id);
            if ($eventModel->getId()) {
                try {
                    $eventModel->delete();
                    $message = 'Email was successfully deleted.';
                    $this->messageManager->addSuccess(__($message));
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    if (!$this->getRequest()->isAjax()) {
                        return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                    }
                }
            }
        }
        if ($this->getRequest()->isAjax()) {
            $jsonData = [];
            $layout = $this->layoutFactory->create();
            $messageBlock = $layout->getMessagesBlock();
            $messageBlock->setMessages($this->messageManager->getMessages(true));
            if ($eventModel->hasData('event_type')) {
                $grid = $layout->createBlock(
                    'Aheadworks\Followupemail\Block\Adminhtml\Event\Grid',
                    'grid_' . $eventModel->getData('event_type')
                );
                $grid->setData('event_type_filter', $eventModel->getData('event_type'));
                $jsonData['grid'] = $grid->toHtml();
            }
            $jsonData['messages'] = $messageBlock->getGroupedHtml();
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($jsonData);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
