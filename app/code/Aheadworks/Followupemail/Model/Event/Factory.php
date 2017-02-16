<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event;

use Magento\Framework\ObjectManagerInterface;

class Factory implements \Magento\Framework\ObjectManager\FactoryInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var Config
     */
    protected $eventConfig;

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $eventConfig
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Config $eventConfig
    ) {
        $this->objectManager = $objectManager;
        $this->eventConfig = $eventConfig;
    }

    /**
     * @param string $requestedType
     * @param array $arguments
     * @return object|void
     */
    public function create($requestedType, array $arguments = [])
    {
        if (!array_key_exists($requestedType, $this->instances)) {
            $config = $this->eventConfig->get($requestedType);
            if ($config) {
                $this->instances[$requestedType] = $this->objectManager->create($config['model'], $arguments);
            } else {
                return null;
            }
        }
        return $this->instances[$requestedType];

    }

    /**
     * Set object manager
     *
     * @param ObjectManagerInterface $objectManager
     *
     * @return void
     */
    public function setObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }
}
