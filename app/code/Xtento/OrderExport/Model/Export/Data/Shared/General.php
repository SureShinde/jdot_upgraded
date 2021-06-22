<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2018-01-29T15:35:29+00:00
 * File:          app/code/Xtento/OrderExport/Model/Export/Data/Shared/General.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Data\Shared;

class General extends \Xtento\OrderExport\Model\Export\Data\AbstractData
{
    public function getConfiguration()
    {
        return [
            'name' => 'Entity fields',
            'category' => 'Shared',
            'description' => 'Export fields from the respective *entity* table.',
            'enabled' => true,
            'apply_to' => [\Xtento\OrderExport\Model\Export::ENTITY_ORDER, \Xtento\OrderExport\Model\Export::ENTITY_INVOICE, \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT, \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO, \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER, \Xtento\OrderExport\Model\Export::ENTITY_QUOTE, \Xtento\OrderExport\Model\Export::ENTITY_AWRMA, \Xtento\OrderExport\Model\Export::ENTITY_BOOSTRMA],
        ];
    }

    public function getExportData($entityType, $collectionItem)
    {
        // Set return array
        $returnArray = [];
        $this->writeArray = & $returnArray; // Write directly on object level
        // Fetch fields to export
        $object = $collectionItem->getObject();

        // Timestamps of creation/update
        if ($this->fieldLoadingRequired('created_at_timestamp')) $this->writeValue('created_at_timestamp', $this->dateHelper->convertDateToStoreTimestamp($object->getCreatedAt()));
        if ($this->fieldLoadingRequired('updated_at_timestamp')) $this->writeValue('updated_at_timestamp', $this->dateHelper->convertDateToStoreTimestamp($object->getUpdatedAt()));

        // Which order line is this?
        $this->writeValue('order_line_number', $collectionItem->currItemNo); // Legacy field
        if ($entityType !== \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
            $this->writeValue('order_count', $collectionItem->collectionSize); // Legacy field
        }
        $this->writeValue('line_number', $collectionItem->currItemNo);
        $this->writeValue('count', $collectionItem->collectionSize);

        // Export information
        $this->writeValue('export_id', $this->_registry->registry('orderexport_log') ? $this->_registry->registry('orderexport_log')->getId() : 0);

        // General data - just not for orders and customers, handled in its own order_general class
        if ($entityType !== \Xtento\OrderExport\Model\Export::ENTITY_ORDER && $entityType !== \Xtento\OrderExport\Model\Export::ENTITY_CUSTOMER) {
            foreach ($object->getData() as $key => $value) {
                $this->writeValue($key, $value);
            }
        } else {
            // Just the entity_id at least for orders
            $this->writeValue('entity_id', $object->getId());
        }

        // Done
        return $returnArray;
    }
}