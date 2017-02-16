<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml\Event\Edit\Tab\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;

class SendEmail extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Followupemail::event/form/renderer/sendemail.phtml';

    /**
     * @var \Aheadworks\Followupemail\Model\Source\Event\Hours
     */
    protected $sourseHours;

    /**
     * @var \Aheadworks\Followupemail\Model\Source\Event\Minutes
     */
    protected $sourseMinutes;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Aheadworks\Followupemail\Model\Source\Event\Hours $soursHours
     * @param \Aheadworks\Followupemail\Model\Source\Event\Minutes $soursMinutes
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Aheadworks\Followupemail\Model\Source\Event\Hours $sourseHours,
        \Aheadworks\Followupemail\Model\Source\Event\Minutes $sourseMinutes,
        array $data = []
    ) {
        $this->sourseHours = $sourseHours;
        $this->sourseMinutes = $sourseMinutes;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        $this->_element->setData('style', 'width: 50px;');
        return $this->toHtml();
    }

    public function getHoursSelectorHtml()
    {
        $value = $this->_element->getData('email_send_hours');
        $optionsHtml = $this->_getOptionsHtml($this->sourseHours, $value);
        return "<select name=\"email_send_hours\">{$optionsHtml}</select>";
    }

    public function getMinutesSelectorHtml()
    {
        $value = $this->_element->getData('email_send_minutes');
        $optionsHtml = $this->_getOptionsHtml($this->sourseMinutes, $value);
        return "<select name=\"email_send_minutes\">{$optionsHtml}</select>";
    }

    public function getAfterLabel()
    {
        return $this->_element->getData('after_label');
    }

    public function isDaysOnly()
    {
        return $this->_element->getData('days_only');
    }

    /**
     * @param \Magento\Framework\Option\ArrayInterface $sourceModel
     * @param $value
     * @return string
     */
    protected function _getOptionsHtml(\Magento\Framework\Option\ArrayInterface $sourceModel, $value)
    {
        $optionsHtml = '';
        foreach ($sourceModel->toOptionArray() as $option) {
            $val = $option['value'];
            $label = __($option['label']);
            $selected = ($value == $val ? "selected='selected'" : "");
            $optionsHtml .= "<option value=\"{$val}\" {$selected}>{$label}</option>";
        }
        return $optionsHtml;
    }
}