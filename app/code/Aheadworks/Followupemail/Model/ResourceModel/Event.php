<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Followupemail\Model\ResourceModel;

/**
 * Event resource model
 */
class Event extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Aheadworks\Followupemail\Model\Event\Config
     */
    protected $eventConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\Collection
     */
    protected $orderStatusCollection;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Aheadworks\Followupemail\Model\Event\Config $eventConfig
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory
     * @param null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Aheadworks\Followupemail\Model\Event\Config $eventConfig,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory,
        $resourcePrefix = null
    ) {
        $this->eventConfig = $eventConfig;
        $this->orderStatusCollection = $orderStatusCollectionFactory->create();
        parent::__construct($context, $resourcePrefix);
    }

    protected function _construct()
    {
        $this->_init('aw_followup_event', 'id');
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew() && !$this->_isEventNameAvailable($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The event name must be unique.')
            );
        }
        if ($object->hasData('stores')) {
            $object->setData('store_ids', implode(',', $object->getData('stores')));
        } else {
            $object->setData('store_ids', '0');
        }
        if ($this->eventConfig->isProductConditionAvailable($object)) {
            if ($object->hasData('product_types')) {
                $object->setData('product_type_ids', implode(',', $object->getData('product_types')));
            } else {
                $object->setData('product_type_ids', 'all');
            }
        }
        if ($object->hasData('customer_groups')) {
            $object->setData('customer_groups', implode(',', $object->getData('customer_groups')));
        } else {
            $object->setData('customer_groups', 'all');
        }
        if ($this->eventConfig->isOrderStatusConditionAvailable($object)) {
            if ($object->hasData('order_statuses')) {
                $orderStatuses = $object->getData('order_statuses');
                if (is_array($orderStatuses)) {
                    $orderStatuses = implode(',', $orderStatuses);
                }
                $object->setData('order_statuses', $orderStatuses);
            } else {
                $object->setData('order_statuses', implode(',', $this->_getOrderStatuses()));
            }
        } else {
            $object->setData('order_statuses', '');
        }

        if ($object->hasData('rule')) {
            $ruleData = $this->_explodeRuleData($object->getData('rule'));
            if (isset($ruleData['cartRule'])) {
                $object->getCartRuleModel()->loadPost($ruleData['cartRule']);
            }
            if (isset($ruleData['productRule'])) {
                $object->getProductRuleModel()->loadPost($ruleData['productRule']);
            }
        }
        $cartCondSerialized = '';
        $productCondSerialized = '';
        if ($object->getCartRuleModel()->getConditions() && $this->eventConfig->isCartConditionAvailable($object)) {
            $cartCondSerialized = serialize($object->getCartRuleModel()->getConditions()->asArray());
            $object->getCartRuleModel()->unsConditions();
        }
        if ($object->getProductRuleModel()->getConditions() && $this->eventConfig->isProductConditionAvailable($object)) {
            $productCondSerialized = serialize($object->getProductRuleModel()->getConditions()->asArray());
            $object->getProductRuleModel()->unsConditions();
        }
        $object->setData('cart_conditions_serialized', $cartCondSerialized);
        $object->setData('product_conditions_serialized', $productCondSerialized);

        return $this;
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $object->setData('stores', explode(',', $object->getData('store_ids')));
            $object->setData('product_types', explode(',', $object->getData('product_type_ids')));
            $object->setData('customer_groups', explode(',', $object->getData('customer_groups')));
            $object->setData('order_statuses', explode(',', $object->getData('order_statuses')));

            $cartCondSerialized = $object->getData('cart_conditions_serialized');
            $productCondSerialized = $object->getData('product_conditions_serialized');
            if ($cartCondSerialized) {
                $object->getCartRuleModel()->setData('conditions_serialized', $cartCondSerialized);
            }
            if ($productCondSerialized) {
                $object->getProductRuleModel()->setData('conditions_serialized', $productCondSerialized);
            }
        }
        return parent::_afterLoad($object);
    }

    protected function _explodeRuleData($data)
    {
        $result = [];

        // todo: move to appropriate models
        $types = [
            'cartRule' => '1',
            'productRule' => '2'
        ];
        foreach ($data['conditions'] as $key => $value) {
            if (substr($key, 0, 1) == $types['cartRule']) {
                $result['cartRule']['conditions'][$key] = $value;
            } elseif (substr($key, 0, 1) == $types['productRule']) {
                $result['productRule']['conditions']['1' . substr($key, 1)] = $value;
            }
        }
        return $result;
    }

    protected function _isEventNameAvailable(\Magento\Framework\Model\AbstractModel $object)
    {
        $readAdapter = $this->getConnection();
        $select = $readAdapter->select();
        $select
            ->from($this->getMainTable(), ['id'])
            ->where('name=?', $object->getData('name'))
        ;
        $result = $readAdapter->fetchOne($select);
        return empty($result);
    }

    public function setDefaultOrderStatuses(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->eventConfig->isOrderStatusConditionAvailable($object)) {
            $object->setData('order_statuses', $this->_getOrderStatuses());
        }
    }

    protected function _getOrderStatuses()
    {
        $statuses = [];
        foreach ($this->orderStatusCollection->getItems() as $item) {
            $statuses[] = $item->getStatus();
        }
        return $statuses;
    }
}