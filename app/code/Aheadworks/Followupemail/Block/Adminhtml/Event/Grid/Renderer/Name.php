<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml\Event\Grid\Renderer;

class Name extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $url = $this->getUrl('followupemail_admin/*/edit', ['id' => $row->getId()]);
        $value = $row->getData('name');
        return <<<HTML
<a href="{$url}">{$value}</a>
HTML;
    }
}
