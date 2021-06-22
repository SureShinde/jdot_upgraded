<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2015-09-05T14:04:51+00:00
 * File:          app/code/Xtento/OrderExport/Helper/Entity.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Helper;

use Magento\Framework\Exception\LocalizedException;

class Entity extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Xtento\OrderExport\Model\Export
     */
    protected $exportModel;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Xtento\OrderExport\Model\Export $exportModel
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Xtento\OrderExport\Model\Export $exportModel,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->exportModel = $exportModel;
    }

    public function getPluralEntityName($entity)
    {
        return $entity;
    }

    public function getEntityName($entity)
    {
        $entities = $this->exportModel->getEntities();
        if (isset($entities[$entity])) {
            return rtrim($entities[$entity], 's');
        } else {
            return __("Undefined Entity");
        }
    }

    public function getExportEntity($entity)
    {
        if ($entity == \Xtento\OrderExport\Model\Export::ENTITY_ORDER) {
            return '\Magento\Sales\Model\Order';
        }
        if ($entity == \Xtento\OrderExport\Model\Export::ENTITY_INVOICE) {
            return '\Magento\Sales\Model\Order\Invoice';
        }
        if ($entity == \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT) {
            return '\Magento\Sales\Model\Order\Shipment';
        }
        if ($entity == \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO) {
            return '\Magento\Sales\Model\Order\Creditmemo';
        }
        if ($entity == \Xtento\OrderExport\Model\Export::ENTITY_QUOTE) {
            return '\Magento\Sales\Model\Quote';
        }
        if ($entity == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
            return '\Magento\Customer\Model\Customer';
        }
        throw new LocalizedException(__('Could not find export entity "%1"', $entity));
    }

    public function getLastIncrementId($entity)
    {
        if ($entity == \Xtento\OrderExport\Model\Export::ENTITY_QUOTE) {
            $collection = $this->objectManager->create($this->getExportEntity($entity))->getCollection()
                ->addFieldToSelect('entity_id');
            $collection->getSelect()->limit(1)->order('entity_id DESC');
        } else {
            if ($entity == \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
                $collection = $this->objectManager->create($this->getExportEntity($entity))->getCollection()
                    ->addAttributeToSelect('entity_id');
                $collection->getSelect()->limit(1)->order('entity_id DESC');
            } else {
                $collection = $this->objectManager->create($this->getExportEntity($entity))->getCollection()
                    ->addAttributeToSelect('increment_id')
                    ->addAttributeToSort('entity_id', 'desc')
                    ->setPage(1, 1);

            }
        }

        $object = $collection->getFirstItem();
        return ($object->getIncrementId() ? $object->getIncrementId() : $object->getId());
    }
}
