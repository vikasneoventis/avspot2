<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Followupemail\Block\Adminhtml\Event\Edit\Tab;

class Conditions extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $objectConverter;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Catalog\Api\ProductTypeListInterface
     */
    protected $productTypeRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\Collection
     */
    protected $orderStatusCollection;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\FieldsetFactory
     */
    protected $rendererFieldsetFactory;

    /**
     * @var \Aheadworks\Followupemail\Model\Event\Config
     */
    protected $eventConfig;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Catalog\Api\ProductTypeListInterface $productTypeRepository
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\FieldsetFactory $rendererFieldsetFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Aheadworks\Followupemail\Model\Event\Config $eventConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Catalog\Api\ProductTypeListInterface $productTypeRepository,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\FieldsetFactory $rendererFieldsetFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Aheadworks\Followupemail\Model\Event\Config $eventConfig,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->objectConverter = $objectConverter;
        $this->groupRepository = $groupRepository;
        $this->productTypeRepository = $productTypeRepository;
        $this->orderStatusCollection = $orderStatusCollectionFactory->create();
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->conditions = $conditions;
        $this->rendererFieldsetFactory = $rendererFieldsetFactory;
        $this->filterBuilder = $filterBuilder;
        $this->eventConfig = $eventConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Aheadworks\Followupemail\Model\Event */
        $model = $this->_coreRegistry->registry('followup_event');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('event_');

        $this->_addGeneralConditionsFieldset($form);
        $this->_addCustomerConditionsFieldset($form, $model);
        $this->_addCartConditionsFieldset($form, $model);
        $this->_addProductConditionsFieldset($form, $model);

        if ($this->_storeManager->isSingleStoreMode()) {
            $model->unsetData('stores');
        }
        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Add general conditions fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     */
    protected function _addGeneralConditionsFieldset(\Magento\Framework\Data\Form $form)
    {
        $fieldset = $form->addFieldset('general_fieldset', ['legend' => __('General Conditions')]);
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'stores',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->systemStore->getStoreValuesForForm(false, true)
                ]
            );
            $field->setRenderer(
                $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element')
            );
        } else {
            $fieldset->setNoContainer(true);
            $fieldset->addField(
                'stores',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
        }
    }

    /**
     * Add customer conditions fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Aheadworks\Followupemail\Model\Event $eventModel
     */
    protected function _addCustomerConditionsFieldset(
        \Magento\Framework\Data\Form $form,
        \Aheadworks\Followupemail\Model\Event $eventModel
    ) {
        if ($this->_availableForEventType($eventModel, 'customer_conditions')) {
            $fieldset = $form->addFieldset('customer_fieldset', ['legend' => __('Customer Conditions')]);

            if (!$this->_availableForEventType($eventModel, 'allowed_for_guests')) {
                $this->searchCriteriaBuilder->addFilters([
                    $this->filterBuilder
                        ->setField('customer_group_id')
                        ->setConditionType('neq')
                        ->setValue(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID)
                        ->create()
                ]);
            }
            $groupsOptions = $this->objectConverter->toOptionArray(
                $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems(),
                'id', 'code'
            );
            array_unshift($groupsOptions, [
                'value' => 'all',
                'label' => __('All groups')
            ]);
            $fieldset->addField(
                'customer_groups',
                'multiselect',
                [
                    'name' => 'customer_groups[]',
                    'label' => __('Customer Groups'),
                    'title' => __('Customer Groups'),
                    'required' => true,
                    'values' =>  $groupsOptions
                ]
            );
        }
    }

    /**
     * Add cart conditions fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Aheadworks\Followupemail\Model\Event $eventModel
     */
    protected function _addCartConditionsFieldset(
        \Magento\Framework\Data\Form $form,
        \Aheadworks\Followupemail\Model\Event $eventModel
    ) {
        if ($this->_availableForEventType($eventModel, 'cart_conditions')) {
            $fieldset = $form->addFieldset(
                    'cart_conditions_fieldset', ['legend' => __('Cart Conditions')]
                )
                ->setRenderer(
                    $this->rendererFieldsetFactory->create()
                        ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
                        ->setNewChildUrl($this->getUrl('sales_rule/promo_quote/newConditionHtml/form/event_cart_conditions_fieldset'))
                )
            ;
            $fieldset
                ->addField(
                    'cart_conditions',
                    'text',
                    ['name' => 'cart_conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
                )
                ->setRule($eventModel->getCartRuleModel())
                ->setRenderer($this->conditions)
            ;
            if ($this->_availableForEventType($eventModel, 'order_statuses_condition')) {
                $fieldset->addField(
                    'order_statuses',
                    'multiselect',
                    [
                        'name' => 'order_statuses[]',
                        'label' => __('Order Statuses'),
                        'title' => __('Order Statuses'),
                        'required' => true,
                        'values' => $this->objectConverter->toOptionArray(
                                $this->orderStatusCollection->getItems(),
                                'status',
                                'label'
                            ),
                    ]
                );
            }
        }
    }

    /**
     * Add product conditions fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Aheadworks\Followupemail\Model\Event $eventModel
     */
    protected function _addProductConditionsFieldset(
        \Magento\Framework\Data\Form $form,
        \Aheadworks\Followupemail\Model\Event $eventModel
    ) {
        if ($this->_availableForEventType($eventModel, 'product_conditions')) {
            $fieldset = $form->addFieldset(
                    'product_conditions_fieldset', ['legend' => __('Products')]
                )
                ->setRenderer($this->rendererFieldsetFactory->create()
                    ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
                    ->setNewChildUrl($this->getUrl('catalog_rule/promo_catalog/newConditionHtml/form/event_product_conditions_fieldset'))
                )
            ;
            $fieldset
                ->addField(
                    'product_conditions',
                    'text',
                    ['name' => 'product_conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
                )
                ->setRule($eventModel->getProductRuleModel())
                ->setRenderer($this->conditions)
            ;
            $productTypesOptions = $this->objectConverter->toOptionArray(
                $this->productTypeRepository->getProductTypes(),
                'name', 'label'
            );
            array_unshift($productTypesOptions, [
                'value' => 'all',
                'label' => __('All Product Types')
            ]);
            $fieldset->addField(
                'product_types',
                'multiselect',
                [
                    'name' => 'product_types[]',
                    'label' => __('Product Types'),
                    'title' => __('Product Types'),
                    'required' => true,
                    'values' => $productTypesOptions
                ]
            );
        }
    }

    /**
     * @param \Aheadworks\Followupemail\Model\Event $eventModel
     * @param string $identifier
     * @return bool
     */
    protected function _availableForEventType(
        \Aheadworks\Followupemail\Model\Event $eventModel,
        $identifier
    ) {
        return array_key_exists(
            $eventModel->getData('event_type'),
            $this->eventConfig->get('*/' . $identifier)
        );
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        /* @var $model \Aheadworks\Followupemail\Model\Event */
        $eventModel = $this->_coreRegistry->registry('followup_event');

        if ($this->_storeManager->isSingleStoreMode()
            && $eventModel->getData('event_type') == 'customer_registration'
        ) {
            return true;
        }
        return false;
    }
}
