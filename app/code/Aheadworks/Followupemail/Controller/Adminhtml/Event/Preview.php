<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Controller\Adminhtml\Event;

use Magento\Backend\App\Action\Context;

class Preview extends \Magento\Backend\App\Action
{
    /**
     * @var \Aheadworks\Followupemail\Model\EventFactory
     */
    protected $eventModelFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @param Context $context
     * @param \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     */
    public function __construct(
        Context $context,
        \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Data\Form\FormKey $formKey
    ) {
        $this->eventModelFactory = $eventModelFactory;
        $this->coreRegistry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKey = $formKey;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aheadworks_Followupemail::home_events');
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $this->_getSession()->setFollowupPreviewData($this->getRequest()->getPostValue());
        } else {
            $this->_view->loadLayout(['followupemail_admin_preview'], true, true, false);
            $data = $this->_getSession()->getFollowupPreviewData();
            if (
                empty($data) ||
                !isset($data['form_key']) || $data['form_key'] !== $this->formKey->getFormKey()
            ) {
                $this->_forward('noroute');
            } else {
                /* @var $eventModel \Aheadworks\Followupemail\Model\Event */
                $eventModel = $this->eventModelFactory->create();
                $eventModel->setData($data);
                $this->coreRegistry->register('preview_model', $eventModel);
                $this->_view->renderLayout();
            }
        }
    }
}
