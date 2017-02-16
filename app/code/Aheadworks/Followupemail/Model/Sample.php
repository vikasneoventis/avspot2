<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model;

class Sample extends \Magento\Framework\Config\Data
{
    /**
     * @param Sample\Reader\Xml $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Aheadworks\Followupemail\Model\Sample\Reader\Xml $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'aheadworks_followupemail_sample_data_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
