<?php
/*
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Edit;
 
/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    
    protected function _construct()
    {
        parent::_construct();
        $this->setId('data_feeds_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Data Feed Manager'));
    }
}
