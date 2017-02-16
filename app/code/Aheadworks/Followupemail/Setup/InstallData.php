<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Aheadworks\Followupemail\Model\EventFactory
     */
    protected $eventModelFactory;

    /**
     * @var \Aheadworks\Followupemail\Model\Sample
     */
    protected $sampleData;

    /**
     * @param \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory
     * @param \Aheadworks\Followupemail\Model\Sample $sampleData
     */
    public function __construct(
        \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory,
        \Aheadworks\Followupemail\Model\Sample $sampleData
    ) {
        $this->eventModelFactory = $eventModelFactory;
        $this->sampleData = $sampleData;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        foreach ($this->sampleData->get() as $data) {
            try {
                $this->eventModelFactory->create()
                    ->setData($data)
                    ->setActive(false)
                    ->save()
                ;
            } catch (\Exception $e) {
            }
        }
    }
}
