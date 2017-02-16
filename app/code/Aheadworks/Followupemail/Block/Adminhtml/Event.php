<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml;

class Event extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var \Aheadworks\Followupemail\Model\Event\Config
     */
    protected $eventConfig;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Aheadworks\Followupemail\Model\Event\Config $eventConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Aheadworks\Followupemail\Model\Event\Config $eventConfig,
        array $data = []
    ) {
        $this->eventConfig = $eventConfig;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        if ($this->_authorization->isAllowed('Aheadworks_Followupemail::home_actions_edit')) {
            $this->addButton(
                'add',
                [
                    'id' => 'create_new_email',
                    'label' => __('Create New Email'),
                    'class' => 'add',
                    'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                    'options' => $this->_getCreateNewEmailButtonOptions()
                ]
            );
        }
    }

    /**
     * @return array
     */
    protected function _getCreateNewEmailButtonOptions()
    {
        $result = [];
        $defaultEvent = 'abandoned_checkout';
        foreach ($this->eventConfig->get() as $type => $data) {
            $result[] = [
                'label' => __($data['title']),
                'onclick' => "setLocation('{$this->getUrl('*/*/edit', ['event_type' => $type])}')",
                'default' => ($type == $defaultEvent)
            ];
        }
        return $result;
    }
}
