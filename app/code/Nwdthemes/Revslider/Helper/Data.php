<?php

namespace Nwdthemes\Revslider\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_systemStore;

    public static $_GET = array();
    public static $_REQUEST = array();

	/**
	 *	Constructor
	 */

	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
		\Magento\Store\Model\System\Store $systemStore
	) {
		$this->_systemStore = $systemStore;

        parent::__construct($context);

		$requestParams = $context->getRequest()->getParams();
        self::$_GET = array_merge(self::$_GET, $requestParams);
        self::$_REQUEST = array_merge(self::$_REQUEST, $requestParams);
	}

    /**
     *  Set page for get imitation
     *
     *  @param  string  Page
     */

    public static function setPage($page = '') {
        self::$_GET['page'] = $page;
    }
	
	/**
	 * Get store options for multiselect
	 *
	 * @return array Array of store options
	 */

	public function getStoreOptions() {
		$storeValues = $this->_systemStore->getStoreValuesForForm(false, true);
		$storeValues = $this->_makeFlatStoreOptions($storeValues);
		return $storeValues;
	}

	/**
	 * Make flat store options
	 *
	 * @param array $storeValues Store values tree array
	 * @retrun array Flat store values array
	 */

	private function _makeFlatStoreOptions($storeValues) {
		$arrStoreValues = array();
		foreach ($storeValues as $_storeValue) {
			if ( ! is_array($_storeValue['value']) ) {
				$arrStoreValues[] = $_storeValue;
			} else {
				$arrStoreValues[] = array(
					'label'	=> $_storeValue['label'],
					'value' => 'option_disabled'
				);
				$_arrSubStoreValues = $this->_makeFlatStoreOptions($_storeValue['value']);
				foreach ($_arrSubStoreValues as $_subStoreValue) {
					$arrStoreValues[] = $_subStoreValue;
				}
			}
		}
		return $arrStoreValues;
	}

}
