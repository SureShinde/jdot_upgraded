<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>.
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 *
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Storepickup\Block\Adminhtml\Tag\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Extended;

class Taggrid extends Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\Product\LinkFactory
     */
    protected $_linkFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magedelight\Subscribenow\Model\PlanProductsFactory
     */
    protected $planProductsFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @param \Magento\Backend\Block\Template\Context                $context
     * @param \Magento\Backend\Helper\Data                           $backendHelper
     * @param \Magento\Catalog\Model\Product\LinkFactory             $linkFactory
     * @param \Magento\Catalog\Model\Product\Type                    $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Catalog\Model\Product\Visibility              $visibility
     * @param \Magento\Framework\Registry                            $coreRegistry
     * @param \Magedelight\Subscribenow\Model\PlanProductsFactory    $_planProductsFactory
     * @param \Magento\Framework\Module\Manager                      $moduleManager
     * @param \Magento\Framework\App\ResourceConnection              $resourceConnection
     * @param array                                                  $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\Product\LinkFactory $linkFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Framework\Registry $coreRegistry,
        \Magedelight\Storepickup\Model\StorelocatorFactory $_storeFactory,
        \Magedelight\Storepickup\Model\TagFactory $_tagFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magedelight\Storepickup\Model\Source\IsActive $isActive,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_linkFactory = $linkFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->_visibility = $visibility;
        $this->_coreRegistry = $coreRegistry;
        $this->_storeFactory = $_storeFactory;
        $this->_tagFactory = $_tagFactory;
        $this->_isActive = $isActive;
        $this->moduleManager = $moduleManager;
        $this->resource = $resourceConnection;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * constructor.
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('plan_product_grid');
        $this->setDefaultSort('tag_id');
        $this->setUseAjax(true);
        $this->setDefaultFilter(['in_products' => 1]);
        if ($this->isReadonly()) {
            $this->setFilterVisibility(false);
        }
    }

    /**
     * prepare layout.
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * @param type $column
     *
     * @return \Magedelight\Subscribenow\Block\Adminhtml\Subscriptionplan\Edit\Tab\Planproducts
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $tagIds = $this->_getSelectedProducts();
            
            if (empty($tagIds)) {
                $tagIds = 0;
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('tag_id', ['in' => $tagIds]);
            } else {
                if ($tagIds) {
                    $this->getCollection()->addFieldToFilter('tag_id', ['nin' => $tagIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * plan products collection.
     */
    protected function _prepareCollection()
    {
        $tagId = $this->getRequest()->getParam('storelocator_id');

      
        /*$collection = $this->_linkFactory->create()
                ->useRelatedLinks()->getProductCollection()->addAttributeToSelect('*')
                ->addAttributeToFilter('type_id', ['neq' => 'grouped'])
                ->addAttributeToFilter(
                        'status', array('eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                )
                ->addFieldToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
        ;

        $tableName = $this->resource->getTableName('magedelight_store_tag');*/

        /*$store = $this->_storeFactory->create();
        $collection = $store->getCollection(); 
        $this->setCollection($collection);*/

        $tag = $this->_tagFactory->create();
        $collection = $tag->getCollection();
        $this->setCollection($collection);

        //$tableName = $this->resource->getTableName('magedelight_store_tag');

        /*$collection->joinTable(
                $this->resource->getTableName('magedelight_store_tag'), 'product_id = entity_id', sprintf('tag_id = %d', $tagId), 'left'
        );*/

        if ($this->isReadonly()) {
            $storeIds = $this->_getSelectedProducts();

            if (empty($tagIds)) {
                $tagIds = [0];
            }

            $collection->addFieldToFilter('tag_id', ['in' => $tagIds]);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();

        /*$collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect(
        '*'
    );
    $this->setCollection($collection);*/
    
    /*$store = $this->_storeFactory->create();
    $collection = $store->getCollection(); 
    $this->setCollection($collection);

    return parent::_prepareCollection();*/
    }

    /**
     * get current product object.
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @return bool
     */
    public function isReadonly()
    {
        return $this->getProduct() && $this->getProduct()->getRelatedReadonly();
    }

    /**
     * get grid columns for plan products.
     */
    protected function _prepareColumns()
    {
        if (!$this->isReadonly()) {
            $this->addColumn(
                'in_products',
                [
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'tag_id',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select',
                    ]
            );
        }
        $this->addColumn(
            'tag_id',
            [
            'header' => __('ID'),
            'sortable' => true,
            'index' => 'tag_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
            'type' => 'number',
                ]
        );

        $this->addColumn(
            'tag_name',
            [
            'header' => __('Name'),
            'index' => 'tag_name',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
                ]
        );

        $this->addColumn(
            'description',
            [
            'header' => __('Description'),
            'index' => 'description',
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn(
            'tag_icon',
            [
            'header' => __('Icon'),
            'index' => 'tag_icon',
            'renderer' => '\Magedelight\Storepickup\Block\Adminhtml\Storelocator\Renderer\TagIcon',
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name'
            ]
        );


    


        $this->addColumn(
            'is_active',
            [
            'header' => __('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'header_css_class' => 'col-type',
            'column_css_class' => 'col-type',
            'options' => $this->_isActive->getOptions()
                ]
        );



        return parent::_prepareColumns();
    }

    /**
     * Rerieve grid URL.
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData(
            'grid_url'
        ) ? $this->getData(
            'grid_url'
        ) : $this->getUrl(
            '*/*/Taggrid',
            ['_current' => true]
        );
    }

    /**
     * Retrieve selected related products.
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        //$products = $this->getPlanProducts();
        $tags = $this->getTags();
      
        if (!is_array($tags)) {
            $products = $this->getSelectedPlanProducts();
        }

        return $products;
    }

    /**
     * Retrieve related products.
     *
     * @return array
     */
    public function getSelectedPlanProducts()
    {
        $tags = [];
        $storeloatorId = $this->getRequest()->getParam('storelocator_id');

        if (!empty($tagId)) {
            $tagCollection = $this->_tagFactory->create();
            $collection = $tagCollection->getCollection()
                        ->addFieldToFilter('tag_id', ['eq' => $tagId]);

          /*$store = $this->_storeFactory->create();
          $collection = $store->getCollection() 
                        ->addFieldToFilter('storelocator_id', ['eq' => $tagId]);*/

/*            $planProductsModel = $this->planProductsFactory->create();
            $planProductsCollection = $planProductsModel->getCollection()
                    ->addFieldToFilter('tag_id', ['eq' => $planId]);*/

            $collectionSize = $collection->getSize();
            if ($collectionSize > 0) {
                $tagData = $collection->getData();
                foreach ($tagData as $data) {
                    if (isset($data['tag_id'])) {
                        $tags = explode('&', $data['store_ids']);
                    }
                }
            }
        }
        return $tags;
    }
}
