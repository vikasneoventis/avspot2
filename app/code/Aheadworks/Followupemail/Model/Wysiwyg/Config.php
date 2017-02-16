<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Wysiwyg;

class Config extends \Magento\Cms\Model\Wysiwyg\Config
{
    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Variable\Model\Variable\Config $variableConfig
     * @param \Magento\Widget\Model\Widget\Config $widgetConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Variable\Config $followupVariableConfig
     * @param array $windowSize
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Variable\Model\Variable\Config $variableConfig,
        \Magento\Widget\Model\Widget\Config $widgetConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Aheadworks\Followupemail\Model\Wysiwyg\Variable\Config $followupVariableConfig,
        \Magento\Framework\Filesystem $filesystem,
        array $windowSize = [],
        array $data = []
    ) {
        parent::__construct(
            $backendUrl,
            $eventManager,
            $authorization,
            $assetRepo,
            $variableConfig,
            $widgetConfig,
            $scopeConfig,
            $storeManager,
            $filesystem,
            $windowSize,
            $data
        );
        $this->_variableConfig = $followupVariableConfig;
    }

    public function getConfig($data = [])
    {
        $this->_variableConfig->setEventType($data['event_type']);
        unset($data['event_type']);
        return parent::getConfig($data);
    }
}
