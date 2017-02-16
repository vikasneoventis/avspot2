<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event;

class Config extends \Magento\Framework\Config\Data
{
    /**
     * @param Config\Reader\Xml $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Aheadworks\Followupemail\Model\Event\Config\Reader\Xml $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'aheadworks_followupemail_config_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }

    public function get($path = null, $default = null)
    {
        if (is_string($path)) {
            $keys = explode('/', $path);
            if (trim($keys[0]) == '*' && count($keys) > 1) {
                $result = [];
                array_shift($keys);
                foreach ($this->_data as $code => $data) {
                    $_data = $data;
                    foreach ($keys as $key) {
                        if (is_array($_data) && array_key_exists($key, $_data)) {
                            $_data = $data[$key];
                        } else {
                            $_data = null;
                        }
                    }
                    if (!is_null($_data)) {
                        $result[$code] = $data;
                    }
                }
                return $result;
            }
        }
        return parent::get($path, $default);
    }

    public function isCartConditionAvailable($event){
        return array_key_exists(
            $event->getData('event_type'),
            $this->get('*/cart_conditions')
        );
    }

    public function isProductConditionAvailable($event){
        return array_key_exists(
            $event->getData('event_type'),
            $this->get('*/product_conditions')
        );
    }

    public function isOrderStatusConditionAvailable($event){
        return array_key_exists(
            $event->getData('event_type'),
            $this->get('*/order_statuses_condition')
        );
    }
}
