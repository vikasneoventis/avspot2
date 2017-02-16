<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml;

class Breadcrumbs extends \Magento\Framework\View\Element\Template
{
    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'breadcrumbs.phtml';

    /**
     * List of available breadcrumb properties
     *
     * @var string[]
     */
    protected $properties = ['label', 'title', 'link', 'first', 'last', 'readonly'];

    /**
     * List of breadcrumbs
     *
     * @var array
     */
    protected $crumbs;

    /**
     * Cache key info
     *
     * @var null|array
     */
    protected $cacheKeyInfo;

    /**
     * Add crumb
     *
     * @param string $crumbName
     * @param array $crumbInfo
     * @return $this
     */
    public function addCrumb($crumbName, $crumbInfo)
    {
        foreach ($this->properties as $key) {
            if (!isset($crumbInfo[$key])) {
                $crumbInfo[$key] = null;
            }
        }

        if (!isset($this->crumbs[$crumbName]) || !$this->crumbs[$crumbName]['readonly']) {
            $this->crumbs[$crumbName] = $crumbInfo;
        }

        return $this;
    }

    /**
     * Get cache key informative items
     *
     * Provide string array key to share specific info item with FPC placeholder
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        if ($this->cacheKeyInfo === null) {
            $this->_cacheKeyInfo = parent::getCacheKeyInfo() + [
                'crumbs' => base64_encode(serialize($this->crumbs)),
                'name' => $this->getNameInLayout(),
            ];
        }
        return $this->cacheKeyInfo;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (is_array($this->crumbs)) {
            reset($this->crumbs);
            $this->crumbs[key($this->crumbs)]['first'] = true;
            end($this->crumbs);
            $this->crumbs[key($this->crumbs)]['last'] = true;
        }
        $this->assign('crumbs', $this->crumbs);

        return parent::_toHtml();
    }
}
