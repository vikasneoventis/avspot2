<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml;

class Menu extends \Magento\Backend\Block\Template
{
    /**
     * @var null|array
     */
    protected $items = null;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Followupemail::menu.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getMenuItems()
    {
        if ($this->items === null) {
            $items = [
                'event' => [
                    'title' => __('Events'),
                    'url' => $this->getUrl('*/event/index'),
                    'resource' => 'Aheadworks_Followupemail::home_events'
                ],
                'queue' => [
                    'title' => __('Mail Log'),
                    'url' => $this->getUrl('*/queue/index'),
                    'resource' => 'Aheadworks_Followupemail::mail_log'
                ],
                'system_config' => [
                    'title' => __('Settings'),
                    'url' => $this->getUrl('adminhtml/system_config/edit', ['section' => 'followupemail'])
                ],
                'readme' => [
                    'title' => __('Readme'),
                    'url' => 'http://confluence.aheadworks.com/display/EUDOC/Follow+Up+Email+-+Magento+2',
                    'attr' => [
                        'target' => '_blank'
                    ],
                    'separator' => true
                ],
                'support' => [
                    'title' => __('Get Support'),
                    'url' => 'http://ecommerce.aheadworks.com/contacts/',
                    'attr' => [
                        'target' => '_blank'
                    ]
                ]
            ];
            foreach ($items as $index => $item) {
                if (array_key_exists('resource', $item)) {
                    if (!$this->_authorization->isAllowed($item['resource'])) {
                        unset($items[$index]);
                    }
                }
            }
            $this->items = $items;
        }
        return $this->items;
    }

    /**
     * @return array
     */
    public function getCurrentItem()
    {
        $items = $this->getMenuItems();
        $controllerName = $this->getRequest()->getControllerName();
        if (array_key_exists($controllerName, $items)) {
            return $items[$controllerName];
        }
        return $items['event'];
    }

    /**
     * @param array $item
     * @return string
     */
    public function renderAttributes(array $item)
    {
        $result = '';
        if (isset($item['attr'])) {
            foreach ($item['attr'] as $attrName => $attrValue) {
                $result .= sprintf(' %s=\'%s\'', $attrName, $attrValue);
            }
        }
        return $result;
    }

    /**
     * @param $itemIndex
     * @return bool
     */
    public function isCurrent($itemIndex)
    {
        return $itemIndex == $this->getRequest()->getControllerName();
    }
}
