<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2016-04-29T11:01:20+00:00
 * File:          app/code/Xtento/OrderExport/Model/Export/Data/Custom/Order/AmastyOrderAttributes.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Custom\Order;

use Xtento\OrderExport\Model\Export;

class AmastyOrderAttributes extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * AmastyOrderAttributes constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateHelper, $utilsHelper, $resource, $resourceCollection, $data);
        $this->objectManager = $objectManager;
        $this->localeDate = $localeDate;
    }

    public function getConfiguration()
    {
        return [
            'name' => 'Amasty Order Attributes Export',
            'category' => 'Order',
            'description' => 'Export custom order attributes of Amasty Order Attributes extension',
            'enabled' => true,
            'apply_to' => [Export::ENTITY_ORDER, Export::ENTITY_INVOICE, Export::ENTITY_SHIPMENT, Export::ENTITY_CREDITMEMO],
            'third_party' => true,
            'depends_module' => 'Amasty_Orderattr',
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray['amasty_orderattributes']; // Write on "amasty_orderattributes" level

        if (!$this->fieldLoadingRequired('amasty_orderattributes')) {
            return $returnArray;
        }

        // Fetch fields to export
        $order = $collectionItem->getOrder();

        try {
            $orderAttributeValue = $this->objectManager->get('\Amasty\Orderattr\Model\Order\Attribute\Value');
            $orderAttributeValue->loadByOrderId($order->getId());
            $attributeMetadataDataProvider = $this->objectManager->get('\Amasty\Orderattr\Model\AttributeMetadataDataProvider');
            $attributeCollection = $attributeMetadataDataProvider->loadAttributesForEditFormByStoreId($order->getStoreId());
            if ($attributeCollection->getSize()) {
                foreach ($attributeCollection as $attribute) {
                    $value = $this->prepareAttributeValue($orderAttributeValue, $attribute);
                    if ($attribute->getFrontendLabel() && $value) {
                        $this->writeValue($attribute->getAttributeCode(), str_replace('$', '\$', $value));
                    }
                }
            }
        } catch (\Exception $e) {

        }

        // Done
        return $returnArray;
    }

    protected function prepareAttributeValue($orderAttributeValue, $attribute)
    {
        $value = $orderAttributeValue->getData($attribute->getAttributeCode());
        switch ($attribute->getFrontendInput())
        {
            case 'select':
            case 'boolean':
            case 'radios':
                $value = $attribute->getSource()->getOptionText($value);
                break;
            case 'date':
                $value = $this->localeDate->formatDate($value);
                break;
            case 'datetime':
                $value = $this->localeDate->formatDateTime($value);
                break;
            case 'checkboxes':
                $value = explode(',', $value);
                $labels = [];
                foreach ($value as $item) {
                    $labels[] = $attribute->getSource()->getOptionText($item);
                }
                $value = implode(', ', $labels);
                break;
        }

        return $value;
    }
}