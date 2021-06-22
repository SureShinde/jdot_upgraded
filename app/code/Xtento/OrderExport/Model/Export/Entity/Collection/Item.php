<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2016-02-26T22:35:00+00:00
 * File:          app/code/Xtento/OrderExport/Model/Export/Entity/Collection/Item.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Entity\Collection;

class Item extends \Magento\Framework\DataObject
{
    public $collectionItem;
    public $collectionSize;
    public $currItemNo;

    public function __construct($collectionItem, $entityType, $currItemNo, $collectionCount)
    {
        parent::__construct();
        $this->collectionItem = $collectionItem;
        $this->collectionSize = $collectionCount;
        $this->currItemNo = $currItemNo;
        if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_ORDER) {
            $this->setOrder($collectionItem);
        }
        if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_INVOICE) {
            $this->setOrder($collectionItem->getOrder());
            $this->setInvoice($collectionItem);
        }
        if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT) {
            $this->setOrder($collectionItem->getOrder());
            $this->setShipment($collectionItem);
        }
        if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO) {
            $this->setOrder($collectionItem->getOrder());
            $this->setCreditmemo($collectionItem);
        }
        if ($entityType == \Xtento\OrderExport\Model\Export::ENTITY_QUOTE) {
            $this->setOrder($collectionItem);
        }
    }

    public function getObject()
    {
        return $this->collectionItem;
    }
}