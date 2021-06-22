<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2017-11-21T18:47:39+00:00
 * File:          app/code/Xtento/OrderExport/Model/Destination/Email.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Destination;

class Email extends AbstractClass
{
    public function testConnection()
    {
        $this->initConnection();
        if (!$this->getDestination()->getBackupDestination()) {
            $this->getDestination()->setLastResult($this->getTestResult()->getSuccess())->setLastResultMessage($this->getTestResult()->getMessage())->save();
        }
        return $this->getTestResult();
    }

    public function initConnection()
    {
        $this->setDestination($this->destinationFactory->create()->load($this->getDestination()->getId()));
        $testResult = new \Magento\Framework\DataObject();
        $this->setTestResult($testResult);
        $this->getTestResult()->setSuccess(true)->setMessage(__('Ready to send emails.'));
        return true;
    }

    public function saveFiles($fileArray)
    {
        if (empty($fileArray)) {
            return [];
        }
        // Init connection
        $this->initConnection();
        $savedFiles = [];

        /** @var \Magento\Framework\Mail\Message $message */
        $mail = $this->objectManager->create('Magento\Framework\Mail\MessageInterface');

        $mail->setFrom($this->getDestination()->getEmailSender(), $this->getDestination()->getEmailSender());
        foreach (explode(",", $this->getDestination()->getEmailRecipient()) as $email) {
            $email = $this->replaceVariables($email, '');
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($email) . '?=');
        }

        $bodyFiles = [];
        foreach ($fileArray as $filename => $data) {
            if ($this->getDestination()->getEmailAttachFiles()) {
                $attachment = $mail->createAttachment($data);
                $attachment->filename = $filename;
            }
            $savedFiles[] = $filename;
            if (stripos($filename, '.pdf') === false) {
                $bodyFiles[] = $data;
            }
        }

        #$mail->setSubject($this->_replaceVariables($this->getDestination()->getEmailSubject(), $firstFileContent));
        $mail->setSubject('=?utf-8?B?' . base64_encode($this->replaceVariables($this->getDestination()->getEmailSubject(), implode("\n\n", $bodyFiles))) . '?=');
        $mail->setMessageType(\Magento\Framework\Mail\Message::TYPE_TEXT)
            ->setBody(strip_tags($this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles))));
        $mail->setMessageType(\Magento\Framework\Mail\Message::TYPE_HTML)
            ->setBody(nl2br($this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles))));

        try {
            $this->objectManager->create('\Magento\Framework\Mail\TransportInterfaceFactory')->create(['message' => clone $mail])->sendMessage();
        } catch (\Exception $e) {
            $this->getTestResult()->setSuccess(false)->setMessage(__('Error while sending email: %1', $e->getMessage()));
            return false;
        }

        return $savedFiles;
    }

    protected function replaceVariables($string, $content)
    {
        $additionalVariables = $this->_registry->registry('xtento_orderexport_export_variables');
        $additionalVariables = is_array($additionalVariables) ? $additionalVariables : [];

        $replaceableVariables = [
            '%d%' => date('d', $this->dateTime->gmtTimestamp()),
            '%m%' => date('m', $this->dateTime->gmtTimestamp()),
            '%y%' => date('y', $this->dateTime->gmtTimestamp()),
            '%Y%' => date('Y', $this->dateTime->gmtTimestamp()),
            '%h%' => date('H', $this->dateTime->gmtTimestamp()),
            '%i%' => date('i', $this->dateTime->gmtTimestamp()),
            '%s%' => date('s', $this->dateTime->gmtTimestamp()),
            '%exportid%' => ($this->_registry->registry('orderexport_log')) ? $this->_registry->registry('orderexport_log')->getId() : 0,
            '%lastexportedincrementid%' => isset($additionalVariables['/%lastincrementid%/']) ? $additionalVariables['/%lastincrementid%/'] : 0,
            '%customeremail%' => isset($additionalVariables['/%lastcustomeremail%/']) ? $additionalVariables['/%lastcustomeremail%/'] : '',
            '%recordcount%' => ($this->_registry->registry('orderexport_log')) ? $this->_registry->registry('orderexport_log')->getRecordsExported() : 0,
            '%content%' => $content,
        ];
        $string = str_replace(array_keys($replaceableVariables), array_values($replaceableVariables), $string);
        return $string;
    }
}