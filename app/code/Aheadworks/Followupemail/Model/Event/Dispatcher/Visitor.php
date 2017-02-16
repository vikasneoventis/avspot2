<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Dispatcher;

class Visitor extends PluginAbstract
{
    /** @var  \Magento\Customer\Model\Customer */
    protected $customer;

    /**
     * @param \Aheadworks\Followupemail\Model\Event\Dispatcher $dispatcher
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function __construct(
        \Aheadworks\Followupemail\Model\Event\Dispatcher $dispatcher,
        \Magento\Customer\Model\Customer $customer
    ) {
        parent::__construct($dispatcher);
        $this->customer = $customer;
    }

    public function afterSave(
        \Magento\Customer\Model\Visitor\Interceptor $interceptor,
        \Magento\Customer\Model\Visitor $visitor
    ) {
        if ($visitor->getCustomerId()) {
            $this->customer->load($visitor->getCustomerId());
            $this->dispatcher->dispatch('customer_last_activity', array_merge($visitor->getData(), [
                'email' => $this->customer->getEmail(),
                'store_id' => $this->customer->getStoreId(),
                'customer_group_id' => $this->customer->getGroupId(),
                'customer_name' => $this->customer->getName()
            ]));
        }
        return $visitor;
    }
}
