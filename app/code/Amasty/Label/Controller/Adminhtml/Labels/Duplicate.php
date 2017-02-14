<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Label\Controller\Adminhtml\Labels;

class Duplicate extends \Amasty\Label\Controller\Adminhtml\Labels
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Amasty\Label\Model\Labels');
                $model->load($id);
                $model->setId(null);
                $model->setStatus(0);
                $model->save();
                $this->messageManager->addSuccess(__('You have duplicated the label.'));
                $this->_redirect('amasty_label/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t duplicate item right now. Please review the log and try again.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('amasty_label/*/edit', ['id' =>  $id]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to duplicate.'));
        $this->_redirect('amasty_label/*/');
    }
}
