<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model\Event\Type;

class CustomerRegistration extends TypeAbstract
{
    protected $type = 'customer_registration';

    /**
     * @var string
     */
    protected $eventObjectClassName = '\Magento\Customer\Model\Customer';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'customer';

    /**
     * @var string
     */
    protected $referenceDataKey = 'entity_id';
}
