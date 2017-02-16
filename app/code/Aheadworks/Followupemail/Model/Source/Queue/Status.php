<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Source\Queue;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class Status implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $options =
            [
                [
                    'value' => \Aheadworks\Followupemail\Model\Queue::STATUS_PENDING,
                    'label' => __('Pending')
                ],
                [
                    'value' => \Aheadworks\Followupemail\Model\Queue::STATUS_FAILED,
                    'label' => __('Failed')
                ],
                [
                    'value' => \Aheadworks\Followupemail\Model\Queue::STATUS_SENT,
                    'label' => __('Sent')
                ],
                [
                    'value' => \Aheadworks\Followupemail\Model\Queue::STATUS_CANCELLED,
                    'label' => __('Cancelled')
                ]
            ];

        $this->options = $options;
        return $this->options;
    }
}
