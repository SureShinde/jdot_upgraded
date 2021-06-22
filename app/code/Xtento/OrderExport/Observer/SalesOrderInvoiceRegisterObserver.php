<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2016-04-17T13:03:38+00:00
 * File:          app/code/Xtento/OrderExport/Observer/SalesOrderInvoiceRegisterObserver.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Observer;

use Xtento\OrderExport\Model\Export;

class SalesOrderInvoiceRegisterObserver extends AbstractEventObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->handleEvent($observer, self::EVENT_SALES_ORDER_INVOICE_REGISTER, Export::ENTITY_INVOICE);
        $this->handleEvent($observer, self::EVENT_SALES_ORDER_INVOICE_REGISTER, Export::ENTITY_ORDER);
    }
}
