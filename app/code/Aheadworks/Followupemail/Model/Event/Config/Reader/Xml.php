<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Config\Reader;

class Xml extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * Mapping XML name nodes
     *
     * @var array
     */
    protected $_idAttributes = ['/config/event' => 'type'];


    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Aheadworks\Followupemail\Model\Event\Config\Converter\Xml $converter,
        \Aheadworks\Followupemail\Model\Event\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'fue_events.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}
