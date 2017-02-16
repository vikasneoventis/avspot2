<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Controller\Adminhtml\Queue;

class Index extends \Aheadworks\Followupemail\Controller\Adminhtml\Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aheadworks_Followupemail::mail_log');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_getResultPage();
        $resultPage->setActiveMenu('Aheadworks_Followupemail::home');
        $resultPage->getConfig()->getTitle()->prepend(__('Mail Log'));
        return $resultPage;
    }
}
