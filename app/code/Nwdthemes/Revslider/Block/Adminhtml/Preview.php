<?php

namespace Nwdthemes\Revslider\Block\Adminhtml;

use \Nwdthemes\Revslider\Model\Revslider\RevSliderAdmin;

class Preview extends \Magento\Backend\Block\Template {

	protected $_request;
	protected $_frameworkHelper;
	protected $_revSliderOperations;

	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
		\Nwdthemes\Revslider\Helper\Framework $frameworkHelper,
		\Nwdthemes\Revslider\Helper\Plugin $pluginHelper,
        \Nwdthemes\Revslider\Model\Revslider\RevSliderFront $revSliderFront,
        \Nwdthemes\Revslider\Model\Revslider\RevSliderAdmin $revSliderAdmin,
		\Nwdthemes\Revslider\Model\Revslider\RevSliderOperations $revSliderOperations
    ) {
        $this->_request = $context->getRequest();
		$this->_frameworkHelper = $frameworkHelper;
		$this->_revSliderOperations = $revSliderOperations;

        $pluginHelper->loadPlugins();

		parent::__construct($context);
	}

	public function previewOutput() {
        switch ($this->_request->getParam('client_action')) {
            case 'preview_slide' :
                $this->_revSliderOperations->putSlidePreviewByData($this->_request->getParam('data'));
            break;
            case 'preview_slider' :
                $this->_revSliderOperations->previewOutput($this->_request->getParam('sliderid'));
            break;
            case 'preview_slide_backup' :
                RevSliderAdmin::onAjaxAction(false);
            break;
        }
		$this->_frameworkHelper->do_action('wp_footer');
	}

}
