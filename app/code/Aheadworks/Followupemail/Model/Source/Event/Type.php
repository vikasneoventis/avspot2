<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Source\Event;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array|null
     */
    protected $options = null;

    /**
     * @var \Aheadworks\Followupemail\Model\Event\Config
     */
    protected $eventConfig;

    /**
     * @param \Aheadworks\Followupemail\Model\Event\Config $eventConfig
     */
    public function __construct(
        \Aheadworks\Followupemail\Model\Event\Config $eventConfig
    ) {
        $this->eventConfig = $eventConfig;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            foreach ($this->eventConfig->get() as $type => $data) {
                $this->options[] = ['value' => $type, 'label' => $data['title']];
            }
        }
        return $this->options;
    }
}
