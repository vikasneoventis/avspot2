<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Dispatcher\Entity;

class Order extends \Aheadworks\Followupemail\Model\Event\Dispatcher\PluginAbstract
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var string|null
     */
    protected $orderStatus = null;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Interceptor $interceptor
     * @param \Magento\Sales\Model\Order $order
     */
    public function beforeLoad(
        \Magento\Sales\Model\ResourceModel\Order\Interceptor $interceptor,
        \Magento\Sales\Model\Order $order
    ) {
        $this->order = $order;
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Interceptor $interceptor
     * @return \Magento\Sales\Model\ResourceModel\Order\Interceptor
     */
    public function afterLoad(
        \Magento\Sales\Model\ResourceModel\Order\Interceptor $interceptor
    ) {
        $this->orderStatus = $this->order->getStatus();
        return $interceptor;
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Interceptor $interceptor
     * @param \Magento\Sales\Model\Order $order
     */
    public function beforeSave(
        \Magento\Sales\Model\ResourceModel\Order\Interceptor $interceptor,
        \Magento\Sales\Model\Order $order
    ) {
        $this->order = $order;
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Interceptor $interceptor
     * @return \Magento\Sales\Model\ResourceModel\Order\Interceptor
     */
    public function afterSave(
        \Magento\Sales\Model\ResourceModel\Order\Interceptor $interceptor
    ) {
        if ($this->orderStatus != $this->order->getStatus()) {
            $eventData = $this->_prepareEventData();
            $this->dispatcher->dispatch('order_status_changed', $eventData);
        }
        return $interceptor;
    }

    protected function _prepareEventData()
    {
        if ($this->order->getCustomerId()) {
            $customerFirstName = $this->order->getCustomerFirstname();
            $customerName = $this->order->getCustomerFirstname() . ' ' . $this->order->getCustomerLastname();
        } else {
            $customerFirstName = $this->order->getBillingAddress()->getFirstname();
            $customerName = $this->order->getBillingAddress()->getName();
        }
        $data = array_merge($this->order->getData(), [
            'email' => $this->order->getCustomerEmail(),
            'customer_firstname' => $customerFirstName,
            'customer_name' => $customerName
        ]);

        return $data;
    }
}
