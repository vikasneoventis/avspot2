<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Source;

class Variables implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $configVariables = [];

    /**
     * @var array
     */
    protected $configConstructions = [];

    /**
     * Constructor
     */
    public function __construct($eventType)
    {
        $this->configVariables = [];
        $this->configVariables[] = ['value' => 'customer_name', 'label' => __('Customer Name')];
        if (!in_array($eventType, ['order_status_changed'])) {
            $this->configVariables[] = ['value' => 'customer.firstname', 'label' => __('Customer First Name')];
            $this->configVariables[] = ['value' => 'customer.lastname', 'label' => __('Customer Last Name')];
        } else {
            $this->configVariables[] = ['value' => 'customer_firstname', 'label' => __('Customer First Name')];
        }
        $this->configVariables[] = ['value' => 'email', 'label' => __('Customer Email')];
        $this->configVariables[] = ['value' => 'store.name', 'label' => __('Store Name')];
        if (in_array($eventType, ['abandoned_checkout'])) {
            $this->configVariables[] = ['value' => 'quote.subtotal|formatPrice', 'label' => __('Cart Subtotal')];
            $this->configVariables[] = ['value' => 'quote.grand_total|formatPrice', 'label' => __('Cart Grand Total')];
        }
        if (in_array($eventType, ['order_status_changed'])) {
            $this->configVariables[] = ['value' => 'order.getIncrementId()', 'label' => __('Order Increment Id')];
            $this->configVariables[] = ['value' => 'order.status', 'label' => __('Order Status')];
            $this->configVariables[] = ['value' => 'order.subtotal|formatPrice', 'label' => __('Order Subtotal')];
            $this->configVariables[] = ['value' => 'order.grand_total|formatPrice', 'label' => __('Order Grand Total')];
        }
    }

    /**
     * Retrieve option array of store contact variables
     *
     * @param bool $withGroup
     * @return array
     */
    public function toOptionArray($withGroup = false)
    {
        $optionArray = [];
        foreach ($this->configVariables as $variable) {
            $optionArray[] = [
                'value' => '{{var ' . $variable['value'] . '}}',
                'label' => $variable['label'],
            ];
        }
        foreach ($this->configConstructions as $construction) {
            $optionArray[] = [
                'value' => $construction['value'],
                'label' => $construction['label'],
            ];
        }
        if ($withGroup && $optionArray) {
            $optionArray = ['label' => __('Follow Up Email'), 'value' => $optionArray];
        }
        return $optionArray;
    }
}
