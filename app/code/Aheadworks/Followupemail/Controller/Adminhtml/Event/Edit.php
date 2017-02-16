<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Controller\Adminhtml\Event;

use Magento\Backend\App\Action;

class Edit extends \Aheadworks\Followupemail\Controller\Adminhtml\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Aheadworks\Followupemail\Model\EventFactory
     */
    protected $eventModelFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory
    ) {
        $this->coreRegistry = $registry;
        $this->eventModelFactory = $eventModelFactory;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aheadworks_Followupemail::home_events');
    }

    /**
     * Edit email
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /* @var $eventModel \Aheadworks\Followupemail\Model\Event */
        $eventModel = $this->eventModelFactory->create();

        $id = $this->getRequest()->getParam('id');
        $eventType = $this->getRequest()->getParam('event_type');
        if ($id) {
            $eventModel->load($id);
            if (!$eventModel->getId()) {
                $this->messageManager->addError(__('This event no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/*');
            }
        }
        if ($eventType) {
            $eventModel->setEventType($eventType);
        }
        if (!$id) {
            $eventModel->setDefaultOrderStatuses();
        }
        $eventModel->getCartRuleModel()->getConditions()->setJsFormObject('event_cart_conditions_fieldset');
        $eventModel->getProductRuleModel()->getConditions()->setJsFormObject('event_product_conditions_fieldset');

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $eventModel->setData($data);
        }
        $this->coreRegistry->register('followup_event', $eventModel);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_getResultPage();
        $resultPage->setActiveMenu('Aheadworks_Followupemail::home');
        $resultPage->getConfig()->getTitle()->prepend(
            $eventModel->getId() ? sprintf("%s \"%s\"", __('Edit email'), $eventModel->getName()) : __('New email')
        );
        $resultPage->getLayout()->getBlock('breadcrumbs')
            ->addCrumb(
                'events',
                ['label' => __('Events'), 'link' =>$this->getUrl('*/*/index')]
            )
            ->addCrumb(
                'edit',
                ['label' => $eventModel->getId() ? sprintf("%s \"%s\"", __('Edit email'), $eventModel->getName()) : __('Add new email')]
            )
        ;
        return $resultPage;
    }
}
