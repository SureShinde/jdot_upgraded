<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
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
 * @package Magedelight_Storepickup
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Storepickup\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magedelight\Storepickup\Model\Storelocator\Image as ImageModel;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{

    const NAME = 'thumbnail';
    const ALT_FIELD = 'name';

    /**
     * Recipient store image config path
     */
    const XML_PATH_STORE_IMAGE = 'magedelight_storepickup/storeinfo/logo';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */

    /**
     * image model
     *
     * @var \Magedelight\Storepickup\Model\Storelocator\Image
     */
    protected $imageModel;

    /**
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ImageModel $imageModel
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ImageModel $imageModel,
        ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageModel = $imageModel;
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->_assetRepo = $assetRepo;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $_config_image = $this->scopeConfig->getValue(self::XML_PATH_STORE_IMAGE, $storeScope);
            foreach ($dataSource['data']['items'] as & $item) {
                if (!empty($item['storeimage'])) {
                    $item[$fieldName . '_src'] = $this->imageModel->getBaseUrl() . $item[$fieldName];
                    $item[$fieldName . '_alt'] = $item['storename'];
                    $item[$fieldName . '_title'] = $item['storename'];
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        'storepickupadmin/storeinfo/edit',
                        ['storelocator_id' => $item['storelocator_id'], 'store' => $this->context->getRequestParam('store')]
                    );
                    $item[$fieldName . '_orig_src'] = $this->imageModel->getBaseUrl() . $item[$fieldName];
                } elseif (!empty($_config_image)) {
                    $item[$fieldName . '_src'] = $this->imageModel->getBaseUrl() . '/' . $_config_image;
                    $item[$fieldName . '_alt'] = $item['storename'];
                    $item[$fieldName . '_title'] = $item['storename'];
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        'storepickupadmin/storeinfo/edit',
                        ['storelocator_id' => $item['storelocator_id'], 'store' => $this->context->getRequestParam('store')]
                    );
                    $item[$fieldName . '_orig_src'] = $this->imageModel->getBaseUrl() . '/' . $_config_image;
                } else {
                    $_default_image = $this->_assetRepo->getUrl("Magedelight_Storepickup::images/image-default.png");

                    $item[$fieldName . '_src'] = $_default_image;
                    $item[$fieldName . '_alt'] = $item['storename'];
                    $item[$fieldName . '_title'] = $item['storename'];
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        'storepickupadmin/storeinfo/edit',
                        ['storelocator_id' => $item['storelocator_id'], 'store' => $this->context->getRequestParam('store')]
                    );
                    $item[$fieldName . '_orig_src'] = $_default_image;
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ? : self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
