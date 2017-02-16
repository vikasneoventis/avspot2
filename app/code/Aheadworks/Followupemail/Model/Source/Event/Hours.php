<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Source\Event;

class Hours implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array|null
     */
    protected $options = null;

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $units = __('hours');
            $unitsSingle = __('hour');
            for ($hour = 0; $hour < 24; $hour++) {
                $this->options[] = ['value' => $hour, 'label' => $hour . ' ' . ($this->_useSingleUnit($hour) ? $unitsSingle : $units)];
            }
        }
        return $this->options;
    }

    /**
     * @param $value
     * @return bool
     */
    protected function _useSingleUnit($value)
    {
        return in_array($value, [1]);
    }
}
