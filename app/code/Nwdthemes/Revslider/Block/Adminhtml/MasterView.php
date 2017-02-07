<?php

namespace Nwdthemes\Revslider\Block\Adminhtml;

use \Nwdthemes\Revslider\Helper\Data;
use \Nwdthemes\Revslider\Helper\Framework;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderGlobals;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderOperations;

class MasterView extends \Magento\Backend\Block\Template {

	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Nwdthemes\Revslider\Helper\Framework $framework,
		\Nwdthemes\Revslider\Helper\Plugin $pluginHelper,
        \Nwdthemes\Revslider\Helper\Query $query,
        \Nwdthemes\Revslider\Helper\Curl $curl,
        \Nwdthemes\Revslider\Helper\Filesystem $filesystemHelper,
        \Nwdthemes\Revslider\Helper\Images $images,
        \Magento\Framework\App\ResourceConnection $resource,
        \Nwdthemes\Revslider\Model\Revslider\GoogleFonts $googleFonts,
        \Nwdthemes\Revslider\Model\Revslider\RevSliderAdmin $revSliderAdmin
    ) {
		parent::__construct($context);

		$pluginHelper->loadPlugins();

		$revSliderAdmin->onAddScripts();

		$framework->add_filter('revslider_mod_icon_sets', array('\Nwdthemes\Revslider\Model\Revslider\Framework\RevSliderBase', 'set_icon_sets'));
		$framework->do_action('admin_enqueue_scripts');



		$revSliderVersion = RevSliderGlobals::SLIDER_REVISION;

		$wrapperClass = "";
		if(RevSliderGlobals::$isNewVersion == false)
			$wrapperClass = " oldwp";

		$nonce = $framework->wp_create_nonce("revslider_actions");

		$rsop = new RevSliderOperations($framework, $query, $curl, $filesystemHelper, $images, $resource, $googleFonts);
		$glval = $rsop->getGeneralSettingsValues();

		$waitstyle = '';
		if(isset(Data::$_REQUEST['update_shop'])){
			$waitstyle = 'display:block';
		}
		
		$inlineStyles = $framework->getFromRegister('inline_styles');
        $localizeScripts = $framework->getFromRegister('localize_scripts');

		$this->assign([
			'framework' => $framework,
			'revSliderVersion' => $revSliderVersion,
			'wrapperClass' => $wrapperClass,
			'nonce' => $nonce,
			'glval' => $glval,
			'waitstyle' => $waitstyle,
			'inlineStyles' => $inlineStyles,
			'localizeScripts' => $localizeScripts
		]);
	}

}