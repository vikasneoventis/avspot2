<?php
/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\DataFeedManager\Controller\Adminhtml\Feeds;

/**
 * Save data feed action
 */
class Save extends \Wyomind\DataFeedManager\Controller\Adminhtml\Feeds\AbstractFeeds
{

    /**
     * Execute action
     * @return void
     */
    public function execute()
    {
        // check if data sent
        $data = $this->getRequest()->getPost();
        if ($data) {
            $model = $this->_objectManager->create('Wyomind\DataFeedManager\Model\Feeds');

            $id = $this->getRequest()->getParam('id');

            if ($id) {
                $model->load($id);
            }

            
            $toSanitize = [
                'name',
                'path',
                'ftp_host',
                'ftp_port',
                'ftp_login',
                'ftp_password',
                'ftp_dir'
            ];
            
            foreach ($data as $index => $value) {
                if (in_array($index, $toSanitize)) {
                    $value = $this->dfmHelper->stripTagsContent($value);
                }
                $model->setData($index, $value);
            }

            if (!$this->_validatePostData($data)) {
                return $this->resultRedirectFactory->create()->setPath('datafeedmanager/feeds/edit', ['id' => $model->getId(), "_current" => true]);
            }

            try {
                $model->openDestinationFile(false);
                $model->save();

                $this->messageManager->addSuccess(__('The data feed has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                if ($this->getRequest()->getParam('back_i') === "1") {
                    return $this->resultRedirectFactory->create()->setPath('datafeedmanager/feeds/edit', ['id' => $model->getId(), "_current" => true]);
                }

                if ($this->getRequest()->getParam('generate_i') === "1") {
                    $this->getRequest()->setParam('id', $model->getId());
                    return $this->resultForwardFactory->create()->forward("generate");
                }

                $this->_getSession()->setFormData($data);
                return $this->resultRedirectFactory->create()->setPath('datafeedmanager/feeds/index');
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Unable to save the data feed.') . '<br/><br/>' . $e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('datafeedmanager/feeds/edit', ['id' => $model->getId(), "_current" => true]);
            }
        }
        return $this->resultRedirectFactory->create()->setPath('datafeedmanager/feeds/index');
    }
    
    protected function _validatePostData($data)
    {
        $errorNo = true;
        if (!empty($data['layout_update_xml']) || !empty($data['custom_layout_update_xml'])) {
            $validatorCustomLayout = $this->_objectManager->create('Magento\Core\Model\Layout\Update\Validator');
            if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }

            if (!empty($data['custom_layout_update_xml']) && !$validatorCustomLayout->isValid(
                $data['custom_layout_update_xml']
            )
            ) {
                $errorNo = false;
            }

            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->messageManager->addError($message);
            }
        }

        return $errorNo;
    }
}
