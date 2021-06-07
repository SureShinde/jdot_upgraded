<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_SizeChart
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\SizeChart\Block;

class Sizechart extends \Magento\Framework\View\Element\Template
{
    const SIZE_ATTR_IDS_CACHE_KEY = 'prsizechart_size_attr_ids';

    static protected $_categories = [];
    static protected $_categoriesSC = [];

    protected $_coreRegistry;
    protected $_objectManager;
    protected $_attrCollectionFactory;

    protected $_template = 'Plumrocket_SizeChart::sizechart.phtml';

    protected $_sizechart;

    protected $_keys = null;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attrCollectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $context->getRegistry();
        $this->_objectManager = $objectManager;
        $this->_attrCollectionFactory = $attrCollectionFactory;
        parent::__construct($context, $data);
    }


    public function getIdentities()
    {
        $product = $this->getProduct();
        $category = $this->getCategory();
        return [\Magento\Cms\Model\Block::CACHE_TAG . '_' .
            $this->_storeManager->getStore()->getId() . '_' .
            'size_chart_p' . $product->getId() . 'c' . ( $category ? $category->getId() : '' )
        ];
    }


    public function getContent()
    {
        if (!$this->hasData('content')) {
            $this->setData('content', false);
            if ($this->_objectManager->get('Plumrocket\SizeChart\Helper\Data')->moduleEnabled()) {
                if ($sizechart = $this->getSizeChart()) {
                    $this->setData('content',
                        $this->_objectManager->get('Magento\Catalog\Helper\Data')->getPageTemplateProcessor()->filter($sizechart->getContent())
                    );
                }
            }
        }
        return $this->getData('content');
    }


    public function getSizeChart()
    {
        if ($this->_sizechart === null) {
            $this->_sizechart = false;

            if ($sizeChartId = $this->_getPlSizeChartIdByProduct($this->getProduct())) {
                if (is_object($sizeChartId) && $sizeChartId->getId()) {
                    $sizechart = $sizeChartId;
                } else {
                    $sizechart = $this->_objectManager->create('Plumrocket\SizeChart\Model\Sizechart')->load($sizeChartId);
                }
                if ($sizechart->isEnabled()) {
                    $storeId = $sizechart->getStoreId();
                    if (!$storeId || $storeId == $this->_storeManager->getStore()->getId()) {
                        $this->_sizechart = $sizechart;
                    }
                }
            }
        }
        return $this->_sizechart;
    }


    protected function _getPlSizeChartIdByProduct($product)
    {
        if ($product->getPlSizeChart() == -1) {
            return null;
        } else if ($sizechartByRules = $this->_getPlSizeChartByRules($product, true)) {
            return $sizechartByRules;
        } else if ($product->getPlSizeChart()) {
            return $product->getPlSizeChart();
        } else {
            if ($category = $this->getCategory()) {
                if ($sizeChartId = $this->_getPlSizeChartIdByCategory($category)) {
                    return ($sizeChartId > 0) ? $sizeChartId : null;
                }
            }

            $categories = $product->getCategoryCollection()->addAttributeToSelect('pl_size_chart');
            foreach($categories as $category) {
                $sizeChartId = $this->_getPlSizeChartIdByCategory($category);
                if ($sizeChartId > 0) {
                    return $sizeChartId;
                }
            }
        }

        if ($sizechartByRules = $this->_getPlSizeChartByRules($product)) {
            return $sizechartByRules;
        }

        return null;
    }


    protected function _getPlSizeChartIdByCategory($category)
    {
        $categoryId = (int)(is_object($category) ? $category->getId() : $category);
        if (!$categoryId) {
            return null;
        }

        if (isset(self::$_categoriesSC[$categoryId])) {
            return self::$_categoriesSC[$categoryId];
        }

        if (!is_object($category)) {
            if (isset(self::$_categories[$categoryId])) {
                $category = self::$_categories[$categoryId];
            } else {
                $category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
                self::$_categories[$categoryId] = $category;
            }
        } else {
            self::$_categories[$category->getId()] = $category;
        }


        if ($category->getPlSizeChart() == -1) {
            $sizeChartId = -1;
        } else if ($category->getPlSizeChart()) {
            $sizeChartId = $category->getPlSizeChart();
        } else {
            $sizeChartId = $this->_getPlSizeChartIdByCategory($category->getParentId());
        }

        return self::$_categoriesSC[$categoryId] = $sizeChartId;

    }


    protected function _getPlSizeChartByRules($product, $isMain = false)
    {
        $sizecharts = $this->_objectManager->create('Plumrocket\SizeChart\Model\ResourceModel\Sizechart\Collection')
            ->addEnabledFilter()
            ->addFieldToFilter('conditions_serialized', ['neq' => ''])
            ->addFieldToFilter('conditions_is_main', (bool)$isMain)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('conditions_priority', 'DESC');

        $space = $this->_objectManager->get('Plumrocket\SizeChart\Model\Sizechart\Space')->getSpace($product);
        foreach ($sizecharts as $sizechart) {
            $_sizechart = $this->_objectManager->create('Plumrocket\SizeChart\Model\Sizechart')->load($sizechart->getId());
            if ($_sizechart->validate($space)) {
                return $_sizechart;
            }
        }

        return null;
    }


    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    public function getCategory()
    {
        if (!$this->hasData('category')) {
            $this->setData('category', $this->_coreRegistry->registry('category'));
        }
        return $this->getData('category');
    }

    public function getLabel()
    {
        return $this->_scopeConfig->getValue('prsizechart/button_settings/label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getJsLayout()
    {
        $data = $this->jsLayout;
        $data['components']['prsizechart.js']['attributes']  = json_encode(
            [
                'id'     => $this->_sizechart->getId(),
                'params' => $this->getSizeParams(),
                'move'   => !$this->getDiscardMovingButton(),
            ]
        );
        return json_encode($data);
    }

    public function getSizeParams()
    {
        if ($this->getDiscardMovingButton()) {
            return false;
        }
        $keys   = $this->getKeys();
        $result = $this->getAttributesIdsForSize($keys);

        return [
                'attributesIds' => $result,
                'keys' => $keys
            ];
    }

    public function getAttributesIdsForSize($keys)
    {
        $cacheKey = self::SIZE_ATTR_IDS_CACHE_KEY . md5(implode(',', $keys));
        $ids = $this->_cache->load($cacheKey);
        if ($ids) {
            return json_decode($ids);
        } else {
            $arrayForFilter = [];
            foreach ($keys as $key) {
                $arrayForFilter[] = ['like' => '%' . $key . '%'];
            }

            $attributeInfo = $this->_attrCollectionFactory->create()->addFieldToFilter('attribute_code', $arrayForFilter);

            $ids = $attributeInfo->getAllIds();

            $this->_cache->save(json_encode($ids), $cacheKey, ['sizechart_attributeIds_cache'], 15*60);
            return  $ids;
        }
    }

    public function getKeys()
    {
        if ($this->_keys === null) {
            $keys = $this->_scopeConfig->getValue(
                'prsizechart/additional/size_attributes',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $keys = explode(',', $keys);
            $result = [];
            foreach ($keys as $value) {
                $key = strtolower(trim($value));
                if ($key) {
                    $result[] = $key;
                }
            }

            if (!in_array('size', $result)) {
                $result[] = 'size';
            }
            $this->_keys = $result;
        }

        return $this->_keys;
    }
}