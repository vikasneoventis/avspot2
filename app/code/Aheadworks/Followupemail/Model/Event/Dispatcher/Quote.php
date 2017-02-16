<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Dispatcher;

class Quote extends PluginAbstract
{
    /**
     * @param \Magento\Quote\Model\Quote\Interceptor $interceptor
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Quote\Model\Quote
     */
    public function afterAfterSave(
        \Magento\Quote\Model\Quote\Interceptor $interceptor,
        \Magento\Quote\Model\Quote $quote
    ) {
        if (
            $quote->getIsActive() &&
            $quote->getItemsCount() > 0 &&
            $quote->getCustomerEmail()
        ) {
            $this->dispatcher->dispatch('abandoned_checkout', array_merge($quote->getData(), [
                'email' => $quote->getCustomerEmail(),
                'customer_name' => $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname()
            ]));
        }
        return $quote;
    }
}
