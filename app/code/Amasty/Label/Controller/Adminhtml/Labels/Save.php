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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;

class Save extends \Amasty\Label\Controller\Adminhtml\Labels
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Amasty\Label\Model\Labels');
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                    ['from_date' => $this->_dateFilter, 'to_date' => $this->_dateFilter],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('label_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getLabelId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong label is specified.'));
                    }
                }
                if (isset($data['customer_group_ids'])) {
                    $data['customer_group_ids'] = serialize($data['customer_group_ids']);
                }
                /*if only one store exists*/
                if (isset($data['stores']) && !$data['stores']) {
                    $data['stores'] = 1;
                }
                if(is_array($data['stores'])) {
                    $data['stores'] = implode(',', $data['stores']);
                }

                if (isset($data['rule']) && isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];

                    unset($data['rule']);

                    $rule = $this->_objectManager->create('Amasty\Label\Model\Rule');
                    $rule->loadPost($data);

                    $data['cond_serialize'] = serialize($rule->getConditions()->asArray());
                    unset($data['conditions']);
                }

                if (!empty($data['to_time'])) {
                    $data['to_date'] = $data['to_date'] . ' ' . $data['to_time'];
                }

                if (!empty($data['from_time'])) {
                    $data['from_date'] = $data['from_date'] . ' ' . $data['from_time'];
                }

                $data = $this->_saveColorSize($data);
                $model->setData($data);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());

                $this->_prepareForSave($model);

                $model->save();

                $this->messageManager->addSuccess(__('You saved the label.'));
                $session->setPageData(false);

                $this->_appCache->invalidate('full_page');

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_label/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('amasty_label/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('amasty_label/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amasty_label/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('amasty_label/*/edit', ['id' =>  $id]);
                return;
            }
        }
        $this->_redirect('amasty_label/*/');
    }


    protected function _prepareForSave($model)
    {
        //upload images
        $data = $this->getRequest()->getPost();
        $path = $this->_filesystem->getDirectoryRead(
                DirectoryList::MEDIA
            )->getAbsolutePath(
            'amasty/amlabel/'
        );

        $this->_ioFile->checkAndCreateFolder($path);

        $imagesTypes = array('prod', 'cat');
        foreach ($imagesTypes as $type) {
            $field = $type . '_img';
            $radioField = 'label_typelabels_' . $type . '_img';

            if ($data[$radioField] == 'downloadlabels_' . $type . '_img') {
                $files = $this->getRequest()->getFiles();
                $isRemove = array_key_exists('remove_labels_' . $field, $data);
                $hasNew = !empty($files[$field]['name']);

                try {
                    // remove the old file
                    if ($isRemove || $hasNew) {
                        $oldName = isset($data['old_labels_' . $field]) ? $data['old_labels_' . $field] : '';
                        if ($oldName) {
                            $this->_ioFile->rm($path . $oldName);
                            $model->setData($field, '');
                        }
                    }

                    // upload a new if any
                    if (!$isRemove && $hasNew) {
                        //find the first available name
                        $newName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $files[$field]['name']);
                        if (substr($newName, 0, 1) == '.') // all non-english symbols
                            $newName = 'label' . $newName;
                        $i = 0;
                        while ($this->_ioFile->fileExists($path . $newName)) {
                            $newName = (++$i) . $newName;
                        }

                        /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                        $uploader = $this->_fileUploaderFactory->create(['fileId' => $field]);
                        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->save($path, $newName);

                        $model->setData($field, $newName);
                    }
                } catch (\Exception $e) {
                    if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                        $this->messageManager->addError($e->getMessage());
                    }
                }
            }
            else{
                $shapeField = 'shape_typelabels_' . $type . '_img';
                if(array_key_exists($shapeField, $data) && $data[$shapeField]) {
                    $shape = $data[$shapeField];
                    $color = $type . '_label_color';
                    $color = $data[$color];
                    $fileName = $this->shapehelper->generateNewLabel($shape, $color);
                    $field = $type . '_img';
                    $model->setData($field, $fileName);
                }
            }
        }

        return true;
    }

    protected function _saveColorSize($data){
        $catStyles = $data['cat_style'];
        if(array_key_exists('cat_size', $data) && $data['cat_size']){
            $size = 'font-size: ' . $data['cat_size'] . ';';
            if(strpos($catStyles, 'font-size') !== FALSE) {
                $catStyles = preg_replace("@font-size(.*?);@s", $size, $catStyles);
            }
            else{
                $catStyles .= $size;
            }
        }
        if(array_key_exists('cat_color', $data) && $data['cat_color']){
            $color = 'color: ' . $data['cat_color'] . ';';
            if(strpos($catStyles, 'color:') !== FALSE) {
                $catStyles = preg_replace("@color(.*?);@s", $color, $catStyles);
            }
            else{
                $catStyles .= $color;
            }
        }

        $catStyles = str_replace(";;", ";", $catStyles);
        $data['cat_style'] = $catStyles;

        $prodStyles = $data['prod_style'];
        if(array_key_exists('prod_size', $data) && $data['prod_size']){
            $size = 'font-size: ' . $data['prod_size'] . ';';
            if(strpos($prodStyles, 'font-size') !== FALSE) {
                $prodStyles = preg_replace("@font-size(.*?);@s", $size, $prodStyles);
            }
            else{
                $prodStyles .= $size;
            }
        }
        if(array_key_exists('prod_color', $data) && $data['prod_color']){
            $color = 'color: ' . $data['prod_color'] . ';';
            if(strpos($prodStyles, 'color:') !== FALSE) {
                $prodStyles = preg_replace("@color(.*?);@s", $color, $prodStyles);
            }
            else{
                $prodStyles .= $color;
            }
        }

        $prodStyles = str_replace(";;", ";", $prodStyles);
        $data['prod_style'] = $prodStyles;

        return $data;
    }
}
