<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Controller\Adminhtml\Event;

class ChangeStatus extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

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
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->layoutFactory = $layoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eventModelFactory = $eventModelFactory;
        $this->coreRegistry = $registry;
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
        /* @var $eventModel \Aheadworks\Followupemail\Model\Event */
        $eventModel = $this->eventModelFactory->create();
        if ($id) {
            $eventModel->load($id);
            if ($eventModel->getId()) {
                $eventModel->setData('active', !(bool)$eventModel->getData('active'));
                try {
                    $eventModel->save();
                    $this->coreRegistry->register('followup_event_id_update_success', $eventModel->getId());
                } catch (\Exception $e) {
                    $this->coreRegistry->register('followup_event_id_update_error', $eventModel->getId());
                }
            }
        }
        $jsonData = [];
        if ($eventModel->hasData('event_type')) {
            $grid = $this->layoutFactory->create()->createBlock(
                'Aheadworks\Followupemail\Block\Adminhtml\Event\Grid',
                'grid_' . $eventModel->getData('event_type')
            )->setData('event_type_filter', $eventModel->getData('event_type'));
            $jsonData['grid'] = $grid->toHtml();
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($jsonData);
    }
}
