<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2016-10-26T21:20:16+00:00
 * File:          app/code/Xtento/OrderExport/Model/Log.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model;

/**
 * Class Log
 * Log model which keeps track of successful/failed export attempts
 * @package Xtento\OrderExport\Model
 */
class Log extends \Magento\Framework\Model\AbstractModel
{
    protected $resultMessages = [];

    // Log result types
    const RESULT_NORESULT = 0;
    const RESULT_SUCCESSFUL = 1;
    const RESULT_WARNING = 2;
    const RESULT_FAILED = 3;

    protected function _construct()
    {
        $this->_init('Xtento\OrderExport\Model\ResourceModel\Log');
        $this->_collectionName = 'Xtento\OrderExport\Model\ResourceModel\Log\Collection';
    }

    public function setResult($resultLevel)
    {
        if ($this->getResult() === NULL) {
            $this->setData('result', $resultLevel);
        } else if ($resultLevel > $this->getResult()) { // If result is failed, do not reset to warning for example.
            $this->setData('result', $resultLevel);
        }
        return $this;
    }

    public function addResultMessage($message)
    {
        array_push($this->resultMessages, $message);
        return $this;
    }

    public function getResultMessages()
    {
        if (empty($this->resultMessages)) {
            return false;
        }
        return (count($this->resultMessages) > 1) ? implode("\n", $this->resultMessages) : $this->resultMessages[0];
    }
}