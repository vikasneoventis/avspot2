<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Sample\Converter;

/**
 * Converts event's parameters from XML files
 */
class Xml implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Converting data to array type
     *
     * @param mixed $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];
        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        $events = $source->getElementsByTagName('event');
        foreach ($events as $event) {
            $eventData = [];
            /** @var $event \DOMElement */
            foreach ($event->childNodes as $child) {
                if (!$child instanceof \DOMElement) {
                    continue;
                }
                /** @var $event \DOMElement */
                $eventData[$child->nodeName] = $child->nodeValue;
            }
            $output[] = $eventData;
        }
        return $output;
    }
}
