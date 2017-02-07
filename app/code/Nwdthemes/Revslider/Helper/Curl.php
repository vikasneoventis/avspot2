<?php

namespace Nwdthemes\Revslider\Helper;

class Curl extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_context;
    protected $_framework;

	/**
	 *	Constructor
	 */

	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Nwdthemes\Revslider\Helper\Framework $framework
    ) {
        $this->_context = $context;
        $this->_framework = $framework;

        parent::__construct($this->_context);
	}

    /**
     *  Check if Curl available
     *
     *  @return boolean
     */

    public function test() {
        $test = function_exists('curl_version');
		return $test;
    }

    /**
     *  Do request
     *
     *  @param  string  url
     *  @return array
     */

    public function request($url) {
        $result = $this->_framework->wp_remote_post($url);
        return $result;
    }

}