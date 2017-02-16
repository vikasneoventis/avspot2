<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml\Event;

class Home extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Aheadworks\Followupemail\Model\Event\Config
     */
    protected $eventConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Aheadworks\Followupemail\Model\Event\Config $eventConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Aheadworks\Followupemail\Model\Event\Config $eventConfig,
        array $data = []
    ) {
        $this->eventConfig = $eventConfig;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        foreach ($this->getEventTypes() as $eventType) {
            $this->addChild(
                'grid_' . $eventType['code'],
                'Aheadworks\Followupemail\Block\Adminhtml\Event\Grid'
            )->setData('event_type_filter', $eventType['code']);
        }
        return $this;
    }

    public function getEventTypes()
    {
        $result = [];
        foreach ($this->eventConfig->get() as $type => $data) {
            $result[] = [
                'code' => $type,
                'title' => __($data['title']),
                'description' => __($data['description'])
            ];
        }
        return $result;
    }

    public function getGridHtml($eventTypeCode)
    {
        return $this->getChildHtml('grid_' . $eventTypeCode);
    }
}
