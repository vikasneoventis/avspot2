<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Controller\Adminhtml\Variable;

class WysiwygPlugin extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Variable\Model\Variable
     */
    protected $customVariable;

    /**
     * @var \Magento\Email\Model\Source\Variables
     */
    protected $contactVariables;

    /**
     * @var \Aheadworks\Followupemail\Model\Source\VariablesFactory
     */
    protected $followupVariablesFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Variable\Model\Variable $customVariable
     * @param \Magento\Email\Model\Source\Variables $contactVariables
     * @param \Aheadworks\Followupemail\Model\Source\VariablesFactory $followupVariablesFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Variable\Model\Variable $customVariable,
        \Magento\Email\Model\Source\Variables $contactVariables,
        \Aheadworks\Followupemail\Model\Source\VariablesFactory $followupVariablesFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customVariable = $customVariable;
        $this->contactVariables = $contactVariables;
        $this->followupVariablesFactory= $followupVariablesFactory;
    }

    public function execute()
    {
        $variables = [];
        $variables[] = $this->customVariable->getVariablesOptionArray(true);
        $variables[] = $this->contactVariables->toOptionArray(true);
        $eventType = $this->getRequest()->getParam('event_type');
        if ($eventType) {
            $variables[] = $this->followupVariablesFactory->create(['eventType' => $eventType])->toOptionArray(true);
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($variables);
    }
}