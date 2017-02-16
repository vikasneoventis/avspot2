<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Type;

class CustomerLastActivity extends TypeAbstract
{
    protected $type = 'customer_last_activity';

    /**
     * @var string
     */
    protected $eventObjectClassName = '\Magento\Customer\Model\Visitor';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'visitor';

    /**
     * @var string
     */
    protected $referenceDataKey = 'visitor_id';
}
