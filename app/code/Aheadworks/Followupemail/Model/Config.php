<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Model;

/**
 * Config Model
 */
class Config extends \Magento\Framework\Model\AbstractModel
{
    /*
     * Last time FUE job ran
     */
    const LAST_EXEC_TIME = 'last_exec_time';

    /*
     * Last time FUE daily job ran
     */
    const LAST_EXEC_TIME_DAILY = 'last_exec_time_daily';


    protected function _construct()
    {
        $this->_init('Aheadworks\Followupemail\Model\ResourceModel\Config');
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setParam($name, $value)
    {
        $this
            ->unsetData()
            ->load($name, 'name')
            ->addData([
                'name' => $name,
                'value' => $value
            ])
            ->save();

        return $this;
    }

    public function getParam($name)
    {
        $this
            ->unsetData()
            ->load($name, 'name');

        return $this->getData('value');

    }
}