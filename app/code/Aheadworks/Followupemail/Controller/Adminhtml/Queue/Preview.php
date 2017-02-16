<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;

class Preview extends \Magento\Backend\App\Action
{
    /**
     * @var \Aheadworks\Followupemail\Model\QueueFactory
     */
    protected $queueFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param Context $context
     * @param \Aheadworks\Followupemail\Model\QueueFactory $queueFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        \Aheadworks\Followupemail\Model\QueueFactory $queueFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->queueFactory = $queueFactory;
        $this->coreRegistry = $registry;
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
        $this->_view->loadLayout(['followupemail_admin_preview'], true, true, false);
        $data = $this->getRequest()->getParams();
        if (!empty($data) && isset($data['id'])) {
            $queueModel = $this->queueFactory->create()->load($data['id']);
            if ($queueModel->getId()) {
                $this->coreRegistry->register('preview_model', $queueModel);
                $this->_view->renderLayout();
                return;
            }
        }
        $this->_forward('noroute');
    }
}
