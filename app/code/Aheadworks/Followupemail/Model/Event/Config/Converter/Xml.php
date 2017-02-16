<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Config\Converter;

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
            /** @var $event \DOMElement */
            if (!$event->hasAttribute('type')) {
                throw new \InvalidArgumentException('Attribute "type" does not exist');
            }
            foreach ($event->childNodes as $child) {
                if (!$child instanceof \DOMElement) {
                    continue;
                }
                /** @var $event \DOMElement */
                $output[$event->getAttribute('type')][$child->nodeName] = $child->nodeValue;
            }
        }
        return $output;
    }
}
