<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Dispatcher\Entity;

class Customer extends \Aheadworks\Followupemail\Model\Event\Dispatcher\PluginAbstract
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * New flag
     *
     * @var bool
     */
    protected $isNew = false;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\Interceptor $interceptor
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function beforeSave(
        \Magento\Customer\Model\ResourceModel\Customer\Interceptor $interceptor,
        \Magento\Customer\Model\Customer $customer
    ) {
        $this->customer = $customer;
        $this->isNew = $this->customer->isObjectNew();
    }

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\Interceptor $interceptor
     * @return \Magento\Customer\Model\ResourceModel\Customer\Interceptor
     */
    public function afterSave(
        \Magento\Customer\Model\ResourceModel\Customer\Interceptor $interceptor
    ) {
        if ($this->isNew && $this->customer->getWebsiteId()) {
            $this->dispatcher->dispatch('customer_registration', array_merge($this->customer->getData(), [
                'customer_name' => $this->customer->getName(),
                'customer_group_id' => $this->customer->getGroupId()
            ]));
        }
        return $interceptor;
    }
}
