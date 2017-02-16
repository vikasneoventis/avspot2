<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model;


/**
 * Event model
 *
 * @method string getSubject() getSubject()
 * @method string getContent() getContent()
 * @method string getEventType() getEventType()
 */
class Event extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Aheadworks\Followupemail\Model\Rule\CartRule
     */
    protected $cartRule;

    /**
     * @var \Aheadworks\Followupemail\Model\Rule\ProductRule
     */
    protected $productRule;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Event $resource
     * @param ResourceModel\Event\Collection $resourceCollection
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param Rule\CartRuleFactory $cartRuleFactory
     * @param Rule\ProductRuleFactory $productRuleFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Followupemail\Model\ResourceModel\Event $resource = null,
        \Aheadworks\Followupemail\Model\ResourceModel\Event\Collection $resourceCollection = null,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Aheadworks\Followupemail\Model\Rule\CartRuleFactory $cartRuleFactory,
        \Aheadworks\Followupemail\Model\Rule\ProductRuleFactory $productRuleFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        $this->cartRule = $cartRuleFactory->create();
        $this->productRule = $productRuleFactory->create();
        $this->productFactory = $productFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    protected function _construct()
    {
        $this->_init('Aheadworks\Followupemail\Model\ResourceModel\Event');
    }

    public function setDefaultOrderStatuses()
    {
        return $this->_getResource()->setDefaultOrderStatuses($this);
    }

    public function getCartRuleModel()
    {
        return $this->cartRule;
    }

    public function getProductRuleModel()
    {
        return $this->productRule;
    }

    public function validate(\Aheadworks\Followupemail\Model\EventHistory $historyItem)
    {
        if (!$this->_validateStores($historyItem)) {
            return false;
        }
        if (!$this->_validateCustomerGroups($historyItem)) {
            return false;
        }
        if (!$this->_validateConditions($historyItem)) {
            return false;
        }
        if (!$this->_validateProductTypes($historyItem)) {
            return false;
        }
        if (!$this->_validateOrderStatuses($historyItem)) {
            return false;
        }
        return true;
    }

    /**
     * Store views validation
     *
     * @param EventHistory $historyItem
     * @return bool
     */
    protected function _validateStores(\Aheadworks\Followupemail\Model\EventHistory $historyItem)
    {
        if (
            !in_array($historyItem->getEventData('store_id'), $this->getData('stores')) &&
            !in_array(0, $this->getData('stores'))
        ) {
            return false;
        }
        return true;
    }

    /**
     * Customer groups validation
     *
     * @param EventHistory $historyItem
     * @return bool
     */
    protected function _validateCustomerGroups(\Aheadworks\Followupemail\Model\EventHistory $historyItem)
    {
        if (
            !in_array($historyItem->getEventData('customer_group_id'), $this->getData('customer_groups')) &&
            !in_array('all', $this->getData('customer_groups'))
        ) {
            return false;
        }
        return true;
    }

    /**
     * Conditions validation
     *
     * @param EventHistory $historyItem
     * @return bool
     */
    protected function _validateConditions(\Aheadworks\Followupemail\Model\EventHistory $historyItem)
    {
        if ($historyItem->hasData('quote') && $this->_hasConditions()) {
            $quote = $historyItem->getData('quote');
            if ($quote->isVirtual()) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }
            foreach ($address->getAllItems() as $item) {
                $product = $this->productFactory->create();
                $product
                    ->setStoreId($historyItem->getEventData('store_id'))
                    ->load($item->getProductId())
                ;
                $item->setProduct($product);
            }
            // Cart conditions validation
            if ($this->getCartRuleModel()->getConditions()->getConditions()) {
                $quote->collectTotals();
                if (!$this->getCartRuleModel()->validate($address)) {
                    return false;
                }
            }
            // Product conditions validation
            if ($this->getProductRuleModel()->getConditions()->getConditions()) {
                $valid = false;
                foreach ($address->getAllItems() as $item) {
                    if ($this->getProductRuleModel()->validate($item->getProduct())) {
                        $valid = true;
                        break;
                    }
                }
                if (!$valid) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Product types validation
     *
     * @param EventHistory $historyItem
     * @return bool
     */
    protected function _validateProductTypes(\Aheadworks\Followupemail\Model\EventHistory $historyItem)
    {
        if (!in_array('all', $this->getData('product_types'))) {
            if ($historyItem->hasData('quote')) {
                foreach ($historyItem->getData('quote')->getItemsCollection() as $quoteItem) {
                    if (
                        $quoteItem->hasData('product') &&
                        !in_array($quoteItem->getData('product')->getTypeId(), $this->getData('product_types'))
                    ) {
                        return false;
                    }
                }
            }
            if ($historyItem->hasData('product')) {
                if (!in_array($historyItem->getData('product')->getTypeId(), $this->getData('product_types'))) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Order statuses validation
     *
     * @param EventHistory $historyItem
     * @return bool
     */
    protected function _validateOrderStatuses(\Aheadworks\Followupemail\Model\EventHistory $historyItem)
    {
        if ($historyItem->hasData('order')) {
            if (!in_array($historyItem->getData('order')->getStatus(), $this->getData('order_statuses'))) {
                return false;
            }
        }
        return true;
    }

    protected function _hasConditions()
    {
        return (
            $this->getCartRuleModel()->getConditions()->getConditions() ||
            $this->getProductRuleModel()->getConditions()->getConditions()
        );
    }

    /**
     * @param $triggerTime
     * @return int
     */
    public function getSendTime($triggerTime)
    {
        return $this->dateTime->timestamp($triggerTime) + $this->_getDeltaTimestamp();
    }

    /**
     * @return int
     */
    protected function _getDeltaTimestamp(){
        return 60 * ($this->getEmailSendMinutes() + 60 * ($this->getEmailSendHours() + $this->getEmailSendDays() * 24));
    }
}