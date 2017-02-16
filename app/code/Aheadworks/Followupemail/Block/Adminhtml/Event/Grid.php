<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml\Event;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    const LIMIT_PER_PAGE_20 = 20;
    const LIMIT_PER_PAGE_30 = 30;
    const LIMIT_PER_PAGE_50 = 50;
    const LIMIT_PER_PAGE_100 = 100;
    const LIMIT_PER_PAGE_200 = 200;

    /**
     * @var \Aheadworks\Followupemail\Model\EventFactory
     */
    protected $eventModelFactory;

    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Followupemail::event/grid.phtml';

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirector;

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_filterVisibility = false;
        $this->_varNameLimit = 'l';
        $this->_varNamePage = 'p';
        $this->_emptyText = __('We couldn\'t find any records.');
    }

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory
     * @param \Magento\Framework\App\Response\RedirectInterface $redirector
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Aheadworks\Followupemail\Model\EventFactory $eventModelFactory,
        \Magento\Framework\App\Response\RedirectInterface $redirector,
        array $data = []
    ) {
        $this->eventModelFactory = $eventModelFactory;
        $this->redirector = $redirector;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->eventModelFactory->create()->getCollection();
        $eventType = $this->getData('event_type_filter');
        $this->setVarNameLimit($this->getVarNameLimit().$eventType);
        $this->setVarNamePage($this->getVarNamePage().$eventType);
        $collection->addFieldToFilter('event_type', ['eq' => $eventType]);
        $this->unsetData('event_type_filter');
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'sortable' => false
            ]
        );
        $this->addColumn(
            'subject',
            [
                'header' => __('Subject'),
                'index' => 'subject',
                'sortable' => false
            ]
        );
        $this->addColumn(
            'when',
            [
                'header' => __('When'),
                'sortable' => false
            ]
        );
        $nameColumn = $this->getColumn('name');
        $renderer = $this->getLayout()->createBlock(
            'Aheadworks\Followupemail\Block\Adminhtml\Event\Grid\Renderer\Name'
        )->setColumn($nameColumn);
        $nameColumn->setRenderer($renderer);
        $whenColumn = $this->getColumn('when');
        $renderer = $this->getLayout()->createBlock(
            'Aheadworks\Followupemail\Block\Adminhtml\Event\Grid\Renderer\When'
        )->setColumn($whenColumn);
        $whenColumn->setRenderer($renderer);

        if ($this->_authorization->isAllowed('Aheadworks_Followupemail::home_actions_edit')) {
            $this->addColumn(
                'action',
                [
                    'header' => __(''),
                    'sortable' => false
                ]
            );
            $actionColumn = $this->getColumn('action');
            $renderer = $this->getLayout()->createBlock(
                'Aheadworks\Followupemail\Block\Adminhtml\Event\Grid\Renderer\Action'
            )->setColumn($actionColumn);
            $actionColumn->setRenderer($renderer);
        }
        return parent::_prepareColumns();
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function isRowDisabled($row)
    {
        return !$row->getData('active');
    }

    /**
     * Grid url getter
     * Version of getGridUrl() but with parameters
     *
     * @param array $params url parameters
     * @return string current grid url
     */
    public function getAbsoluteGridUrl($params = [])
    {
        if ($this->_request->isAjax()) {
            $url = $this->redirector->getRefererUrl();
        } else {
            $params['id'] = null;
            $url = parent::getAbsoluteGridUrl($params);
        }

        return $url;
    }
}
