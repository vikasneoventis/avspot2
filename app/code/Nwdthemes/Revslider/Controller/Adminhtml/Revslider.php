<?php

namespace Nwdthemes\Revslider\Controller\Adminhtml;

abstract class Revslider extends \Magento\Backend\App\Action {

    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

}
