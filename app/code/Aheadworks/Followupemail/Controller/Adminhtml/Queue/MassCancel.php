<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class MassCancel extends \Magento\Backend\App\Action
{
    /**
     * Field id
     */
    const ID_FIELD = 'id';

    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = 'Aheadworks\Followupemail\Model\ResourceModel\Queue\Collection';


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
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');

        $collection = $this->_objectManager->create($this->collection);
        try {
            if (!empty($excluded)) {
                $collection->addFieldToFilter(static::ID_FIELD, ['nin' => $excluded]);
                $this->massAction($collection);
            } elseif (!empty($selected)) {
                $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
                $this->massAction($collection);
            } else {
                $this->messageManager->addError(__('Please select item(s).'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Something went wrong while cancelling the email(s).'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('followupemail_admin/*/');
    }

    protected function massAction($collection)
    {
        $count = 0;
        foreach ($collection->getItems() as $queueItem) {
            if ($queueItem->canCancel()) {
                $queueItem
                    ->setStatus(\Aheadworks\Followupemail\Model\Queue::STATUS_CANCELLED)
                    ->save()
                ;
                ++$count;
            }
        }
        if ($count == 0) {
            $this->messageManager->addWarning(__('None of the selected emails can be cancelled.'));
        } else {
            $this->messageManager->addSuccess(__('A total of %1 email(s) have been cancelled.', $count));
        }
    }

}
