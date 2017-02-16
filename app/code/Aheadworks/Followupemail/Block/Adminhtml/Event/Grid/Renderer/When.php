<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml\Event\Grid\Renderer;

class When extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getEmailSendDays() > 0) {
            $days = sprintf("%s %s ", $row->getEmailSendDays(), $row->getEmailSendDays() > 1 ? __('days') : __('day'));
        }
        if ($row->getEmailSendHours() > 0) {
            $hours = sprintf("%s %s ", $row->getEmailSendHours(), $row->getEmailSendHours() > 1 ? __('hours') : __('hour'));
        }
        if ($row->getEmailSendMinutes() > 0) {
            $minutes = sprintf("%s %s ", $row->getEmailSendMinutes(), __('minutes'));
        }
        $result = (isset($days) ? $days : '') . (isset($hours) ? $hours : '') . (isset($minutes) ? $minutes : '');
        if (empty($result)) {
            return __('Immediately');
        }
        $afterLabel = $row->getEventType() == 'customer_birthday' ? __('before') : __('later');
        return $result . $afterLabel;
    }
}
