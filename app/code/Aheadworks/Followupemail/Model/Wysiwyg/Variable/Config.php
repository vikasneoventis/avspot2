<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Wysiwyg\Variable;

class Config extends \Magento\Variable\Model\Variable\Config
{
    /**
     * @var string
     */
    protected $eventType;

    /**
     * @param string $eventType
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * Return url of action to get variables
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getVariablesWysiwygActionUrl()
    {
        return $this->_url->getUrl('followupemail_admin/variable/wysiwygPlugin', ['event_type' => $this->eventType]);
    }
}
