<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2016-10-20T09:07:10+00:00
 * File:          app/code/Xtento/OrderExport/Model/Output/Xml/Writer.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Output\Xml;

class Writer extends \XMLWriter
{
    protected $escapeSpecialChars = false;

    public function __construct()
    {
        $this->openMemory();
        $this->setIndent(2);
        $this->startDocument('1.0', 'UTF-8');
        $this->startElement('objects');
    }

    public function setEscapeSpecialChars($escapeSpecialChars)
    {
        $this->escapeSpecialChars = $escapeSpecialChars;
    }

    public function setElement($elementName, $elementText)
    {
        $elementName = trim($elementName);
        if (isset($elementName[0]) && is_numeric($elementName[0])) {
            $elementName = '_' . $elementName;
        }
        $this->startElement($elementName);
        $this->text($elementText);
        $this->endElement();
    }

    public function fromArray($array, $parentKey = '')
    {
        if (is_array($array)) {
            foreach ($array as $key => $element) {
                if (is_array($element)) {
                    $key = $this->handleSpecialParentKeys($key, $parentKey);
                    $this->startElement($key);
                    $this->fromArray($element, $key);
                    $this->endElement();
                } elseif (is_string($key)) {
                    $this->setElement($key, $this->stripInvalidXml($element));
                }
            }
        }
    }

    public function getDocument()
    {
        $this->endElement();
        $this->endDocument();
        return $this->outputMemory();
    }

    public function handleSpecialParentKeys($key, $parentKey)
    {
        if (is_numeric($key) && $parentKey == '') {
            $key = 'object';
        }
        $iteratingKeys = \Xtento\OrderExport\Model\Output\AbstractOutput::$iteratingKeys;
        if (is_numeric($key) && $parentKey !== '') {
            if (in_array($parentKey, $iteratingKeys) || isset($iteratingKeys[$parentKey])) {
                if (isset($iteratingKeys[$parentKey])) {
                    $key = $iteratingKeys[$parentKey];
                } else {
                    $key = substr($parentKey, 0, -1);
                }
            }
            // Ensure a valid string key - thanks to Thomas Hägi
            if (is_numeric($key)) {
                // Create pseudo-singular key from parent key if possible
                $len = strlen($parentKey);
                if ($parentKey && $parentKey[$len - 1] == 's') {
                    $key = substr($parentKey, 0, $len - 1);
                } else {
                    $key = 'object';
                }
            }
        }
        return $key;
    }

    protected function stripInvalidXml($string)
    {
        $strippedValue = preg_replace(
            '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u',
            '',
            $string
        );
        #if ($this->escapeSpecialChars) {
            #$strippedValue = htmlspecialchars($strippedValue);
        #}
        return $strippedValue;
    }
}