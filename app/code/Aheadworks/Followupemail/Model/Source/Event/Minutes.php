<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Source\Event;

class Minutes implements \Magento\Framework\Option\ArrayInterface
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
            $units = __('minutes');
            for ($minutes = 0; $minutes < 60; $minutes += 5) {
                $this->options[] = ['value' => $minutes, 'label' => $minutes . ' ' . $units];
            }
        }
        return $this->options;
    }
}
