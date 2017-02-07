<?php

namespace Nwdthemes\Revslider\Block;

use \Nwdthemes\Revslider\Model\Revslider\RevSliderOperations;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderOutput;
use \Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderCssParser;

class Revslider extends \Magento\Framework\View\Element\Template {

	protected $_framework;
	protected $_status;
	protected $_content;
	protected $_slider;

	/**
	 * Constructor
	 */

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Nwdthemes\Revslider\Helper\Framework $framework,
		\Nwdthemes\Revslider\Helper\Plugin $pluginHelper,
		\Nwdthemes\Revslider\Model\Revslider\RevSliderFront $revsliderFront,
		array $data = []
	) {
		$this->_framework = $framework;

        parent::__construct($context, $data);

        $this->setTemplate('Nwdthemes_Revslider::revslider.phtml');

        $this->_status = $this->_scopeConfig->getValue('nwdthemes_revslider/revslider_configuration/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($this->_status) {
            $pluginHelper->loadPlugins();
        }
	}

	protected function _renderSlider() {
		if ( is_null($this->_slider) ) {
			ob_start();
			$this->_slider = RevSliderOutput::putSlider($this->getData('alias'));
			$this->_content = ob_get_contents();
			ob_clean();
			ob_end_clean();
		}
	}

    /**
     *  Include scritps and styles
     */

    protected function addHeadIncludes() {
        $this->_renderSlider();
        $this->_framework->do_action('wp_enqueue_scripts');
        $content = $this->_framework->do_action('wp_footer', 'action_no_output');
        return $content;
    }

	public function getCacheKeyInfo() {
		$this->_renderSlider();
		$key = parent::getCacheKeyInfo();
		$key[] = $this->getData('alias');
		$key[] = $this->_slider->getParam("disable_on_mobile", "off");
		$key[] = isset($_SERVER['HTTPS']);
		return $key;
	}

	public function renderSlider() {
		if ($this->_status) {

			$this->_renderSlider();

			$custom_css = RevSliderOperations::getStaticCss();
			$custom_css = '<style type="text/css">' . RevSliderCssParser::compress_css($custom_css) . '</style>';

			$this->_content = $this->addHeadIncludes() . $custom_css . $this->_content;

			if(!empty($this->_slider)) {

				// Do not output Slider if we are on mobile
				$disable_on_mobile = $this->_slider->getParam("disable_on_mobile","off");
				if($disable_on_mobile == 'on'){
					$mobile = (strstr($_SERVER['HTTP_USER_AGENT'],'Android') || strstr($_SERVER['HTTP_USER_AGENT'],'webOS') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||strstr($_SERVER['HTTP_USER_AGENT'],'iPod') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad') || strstr($_SERVER['HTTP_USER_AGENT'],'Windows Phone') || $this->_framework->wp_is_mobile()) ? true : false;
					if($mobile) return false;
				}

				$show_alternate = $this->_slider->getParam("show_alternative_type","off");

				if($show_alternate == 'mobile' || $show_alternate == 'mobile-ie8'){
					if(strstr($_SERVER['HTTP_USER_AGENT'],'Android') || strstr($_SERVER['HTTP_USER_AGENT'],'webOS') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||strstr($_SERVER['HTTP_USER_AGENT'],'iPod') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad') || strstr($_SERVER['HTTP_USER_AGENT'],'Windows Phone') || $this->_framework->wp_is_mobile()){
						$show_alternate_image = $this->_slider->getParam("show_alternate_image","");
						$this->_content = '<img class="tp-slider-alternative-image" src="'.$show_alternate_image.'" data-no-retina>';
					}
				}
			}
		}
		return $this->_content;
	}

}
