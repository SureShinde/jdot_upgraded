<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2016-03-01T16:14:18+00:00
 * File:          app/code/Xtento/OrderExport/Model/System/Config/Source/Export/Status.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\System\Config\Source\Export;

class Status
{
    /**
     * @var \Xtento\XtCore\Model\System\Config\Source\Order\AllStatuses
     */
    protected $allStatuses;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * Status constructor.
     * @param \Xtento\XtCore\Model\System\Config\Source\Order\AllStatuses $allStatuses
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     */
    public function __construct(
        \Xtento\XtCore\Model\System\Config\Source\Order\AllStatuses $allStatuses,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->allStatuses = $allStatuses;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
    }

    public function toOptionArray($entity)
    {
        $statuses = [];

        if ($entity == \Xtento\OrderExport\Model\Export::ENTITY_ORDER) {
            $statuses = $this->allStatuses->toOptionArray();
            array_shift($statuses); // Remove first entry.
        } else {
            if ($entity == \Xtento\OrderExport\Model\Export::ENTITY_INVOICE) {
                foreach ($this->invoiceRepository->create()->getStates() as $state => $label) {
                    $statuses[] = ['value' => $state, 'label' => $label];
                }
            } else {
                if ($entity == \Xtento\OrderExport\Model\Export::ENTITY_SHIPMENT) {

                } else {
                    if ($entity == \Xtento\OrderExport\Model\Export::ENTITY_CREDITMEMO) {
                        foreach ($this->creditmemoRepository->create()->getStates() as $state => $label) {
                            $statuses[] = ['value' => $state, 'label' => $label];
                        }
                    }
                }
            }
        }

        return $statuses;
    }

    // Function to just put all status "codes" into an array.
    public function toArray($entity)
    {
        $statuses = $this->toOptionArray($entity);
        $statusArray = [];
        foreach ($statuses as $status) {
            $statusArray[$status['value']];
        }
        return $statusArray;
    }
}
