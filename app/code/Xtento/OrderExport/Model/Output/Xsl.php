<?php

/**
 * Product:       Xtento_OrderExport (2.5.2)
 * ID:            ALOS9nyJR4GmLp9b0POAXWBdZQz7n1C/haY72X8BIV4=
 * Packaged:      2018-04-13T12:30:09+00:00
 * Last Modified: 2017-12-12T13:46:33+00:00
 * File:          app/code/Xtento/OrderExport/Model/Output/Xsl.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Output;

use Magento\Framework\Exception\LocalizedException;

class Xsl extends AbstractOutput
{
    protected $searchCharacters;
    protected $replaceCharacters;

    /**
     * @var XmlFactory
     */
    protected $outputXmlFactory;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Invoice
     */
    protected $pdfInvoice;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Shipment
     */
    protected $pdfShipment;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Creditmemo
     */
    protected $pdfCreditmemo;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $invoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory
     */
    protected $creditmemoCollectionFactory;

    /**
     * @var \Magento\Framework\Config\DataInterface
     */
    protected $exportSettings;

    /**
     * Xsl constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\OrderExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\OrderExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory
     * @param \Xtento\OrderExport\Model\ResourceModel\Log\CollectionFactory $logCollectionFactory
     * @param XmlFactory $outputXmlFactory
     * @param \Magento\Sales\Model\Order\Pdf\Invoice $pdfInvoice
     * @param \Magento\Sales\Model\Order\Pdf\Shipment $pdfShipment
     * @param \Magento\Sales\Model\Order\Pdf\Creditmemo $pdfCreditmemo
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemoCollectionFactory
     * @param \Magento\Framework\Config\DataInterface $exportSettings
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\OrderExport\Model\ProfileFactory $profileFactory,
        \Xtento\OrderExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Xtento\OrderExport\Model\ResourceModel\Log\CollectionFactory $logCollectionFactory,
        XmlFactory $outputXmlFactory,
        \Magento\Sales\Model\Order\Pdf\Invoice $pdfInvoice,
        \Magento\Sales\Model\Order\Pdf\Shipment $pdfShipment,
        \Magento\Sales\Model\Order\Pdf\Creditmemo $pdfCreditmemo,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemoCollectionFactory,
        \Magento\Framework\Config\DataInterface $exportSettings,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $localeDate,
            $dateHelper,
            $profileFactory,
            $historyCollectionFactory,
            $logCollectionFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->outputXmlFactory = $outputXmlFactory;
        $this->pdfInvoice = $pdfInvoice;
        $this->pdfShipment = $pdfShipment;
        $this->pdfCreditmemo = $pdfCreditmemo;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->creditmemoCollectionFactory = $creditmemoCollectionFactory;
        $this->exportSettings = $exportSettings;
    }


    public function convertData($exportArray)
    {
        if (!@class_exists('\XSLTProcessor')) {
            throw new LocalizedException(__('The XSLTProcessor class could not be found. This means your PHP installation is missing XSL features. You cannot export output formats using XSL Templates without the PHP XSL extension. Please get in touch with your hoster or server administrator to add XSL to your PHP configuration.'));
        }
        // Some libxml settings, constants
        $libxmlConstants = null;
        if (defined('LIBXML_PARSEHUGE')) {
            $libxmlConstants = LIBXML_PARSEHUGE;
        }
        $useInternalXmlErrors = libxml_use_internal_errors(true);
        #if (function_exists('libxml_disable_entity_loader')) {
            #$loadXmlEntities = libxml_disable_entity_loader(true);
        #}
        libxml_clear_errors();

        $outputArray = [];
        // Should the ampersand character etc. be encoded?
        $escapeSpecialChars = false;
        if (preg_match('/method="(xml|html)"/', $this->getProfile()->getXslTemplate())) {
            $escapeSpecialChars = true;
        }
        // Convert to XML first
        $convertedData = $this->outputXmlFactory->create()->setProfile($this->getProfile())->setEscapeSpecialChars($escapeSpecialChars)->convertData($exportArray);
        // Get "first" file from returned data.
        $convertedXml = array_pop($convertedData);
        // If there are problems with bad/destroyed encodings in the DB:
        // $convertedXml = utf8_encode(utf8_decode($convertedXml));
        $xmlDoc = new \DOMDocument;
        if (!$xmlDoc->loadXML($convertedXml, $libxmlConstants)) {
            $this->throwXmlException(__("Could not load internally processed XML. Bad data maybe?"));
        }
        // Load different file templates
        $outputFormatMarkup = $this->getProfile()->getXslTemplate();
        if (empty($outputFormatMarkup)) {
            throw new LocalizedException(__('No XSL Template has been set up for this export profile. Please open the export profile and set up your XSL Template in the "Output Format" tab.'));
        }
        try {
            $outputFormatXml = new \SimpleXMLElement($outputFormatMarkup, null, strpos($outputFormatMarkup, '<') === false);
        } catch (\Exception $e) {
            $this->throwXmlException(__("Please repair the XSL Template of this profile. You need to have a valid XSL Template in order to export orders. Could not load XSL Template:"));
        }
        $outputFormats = $outputFormatXml->xpath('//files/file');
        if (empty($outputFormats)) {
            throw new LocalizedException(__('No <files><file></file></files> markup found in XSL Template. Please repair your XSL Template.'));
        }
        // Loop through each <file> node
        foreach ($outputFormats as $outputFormat) {
            $fileAttributes = $outputFormat->attributes();
            $filename = $this->replaceFilenameVariables($this->getSimpleXmlElementAttribute($fileAttributes->filename), $exportArray);
            $fileType = $this->getSimpleXmlElementAttribute($fileAttributes->type); // Currently supported: xsl (default), invoice_pdf, packingslip_pdf

            if (!$fileType || empty($fileType) || $fileType == 'xsl') {
                $charsetEncoding = $this->getSimpleXmlElementAttribute($fileAttributes->encoding);
                $charsetLocale = $this->getSimpleXmlElementAttribute($fileAttributes->locale);
                $searchCharacters = $this->getSimpleXmlElementAttribute($fileAttributes->search);
                $replaceCharacters = $this->getSimpleXmlElementAttribute($fileAttributes->replace);
                $quoteHandling = $this->getSimpleXmlElementAttribute($fileAttributes->quotes);
                $addUtf8Bom = ($this->getSimpleXmlElementAttribute($fileAttributes->addUtf8Bom) == 1) ? true : false;

                $xslTemplate = current($outputFormat->xpath('*'))->asXML();
                $xslTemplate = $this->preparseXslTemplate($xslTemplate);

                // XSL Template
                $xslTemplateObj = new \XSLTProcessor();
                $allowedPhpFunctions = array_merge(explode(",", $this->exportSettings->get('allowed_php_functions')), explode(",", $this->exportSettings->get('custom_allowed_php_functions')));
                $xslTemplateObj->registerPHPFunctions($allowedPhpFunctions);
                // Add some parameters accessible as $variables in the XSL Template (example: <xsl:value-of select="$exportid"/>)
                $this->addVariablesToXSLT($xslTemplateObj, $exportArray, $xslTemplate);
                // Import stylesheet
                /* Alternative DOMDocument version for versions that don't like SimpleXMLElements in importStylesheet */
                /*
                $domDocument = new DOMDocument();
                $domDocument->loadXML($xslTemplate);
                $xslTemplateObj->importStylesheet($domDocument);
                */
                $xslTemplateObj->importStylesheet(new \SimpleXMLElement($xslTemplate));
                if (libxml_get_last_error() !== FALSE) {
                    $this->throwXmlException(__("Please repair the XSL Template of this profile. There was a problem processing the XSL Template:"));
                }

                $adjustedXml = false;
                // Replace certain characters
                if (!empty($searchCharacters)) {
                    $this->searchCharacters = str_split(str_replace(['quote'], ['"'], $searchCharacters));
                    if (in_array('"', $this->searchCharacters)) {
                        $replacePosition = array_search('"', $this->searchCharacters);
                        if ($replacePosition !== false) {
                            $this->searchCharacters[$replacePosition] = '&quot;';
                        }
                    }
                    $this->replaceCharacters = str_split($replaceCharacters);
                    $adjustedXml = preg_replace_callback('/<(.*)>(.*)<\/(.*)>/um', [$this, 'replaceCharacters'], $convertedXml);
                }
                // Handle quotes in field data
                if (!empty($quoteHandling)) {
                    $ampSign = '&';
                    if ($escapeSpecialChars) {
                        $ampSign = '&amp;';
                    }
                    if ($quoteHandling == 'double') {
                        $quoteReplaceData = $ampSign . 'quot;' . $ampSign . 'quot;';
                    } else if ($quoteHandling == 'remove') {
                        $quoteReplaceData = '';
                    } else {
                        $quoteReplaceData = $quoteHandling;
                    }
                    if ($adjustedXml !== false) {
                        $adjustedXml = str_replace($ampSign . "quot;", $quoteReplaceData, $adjustedXml);
                    } else {
                        $adjustedXml = str_replace($ampSign . "quot;", $quoteReplaceData, $convertedXml);
                    }
                }
                if ($adjustedXml !== false) {
                    $xmlDoc->loadXML($adjustedXml, $libxmlConstants);
                }

                $outputBeforeEncoding = @$xslTemplateObj->transformToXML($xmlDoc);
                $output = $this->changeEncoding($outputBeforeEncoding, $charsetEncoding, $charsetLocale);
                if (!$output && !empty($outputBeforeEncoding)) {
                    $this->throwXmlException(__("Please repair the XSL Template of this profile, check the encoding tag, or make sure output has been generated by this template. No output has been generated."));
                }
                if ($addUtf8Bom) {
                    $utf8Bom = pack('H*', 'EFBBBF');
                    $output = $utf8Bom . $output;
                }
                $outputArray[$filename] = $output;
            }
            if ($this->_registry->registry('is_test_orderexport') !== true) {
                if ($fileType == 'invoice_pdf' || $fileType == 'packingslip_pdf' || $fileType == 'creditmemo_pdf'
                    || preg_match('/fooman\_/', $fileType)) {
                    $orderIds = [];
                    foreach ($exportArray as $exportObject) {
                        if (isset($exportObject['order']) && isset($exportObject['order']['entity_id'])) {
                            $orderIds[] = $exportObject['order']['entity_id'];
                        } else {
                            $orderIds[] = $exportObject['entity_id'];
                        }
                    }
                    if (!empty($orderIds)) {
                        $pdfContent = $this->getPdfsForOrderIds($orderIds, $fileType);
                        if ($pdfContent) {
                            $outputArray[$filename] = $pdfContent;
                        }
                    }
                }
            }
        }
        // Reset libxml settings
        libxml_use_internal_errors($useInternalXmlErrors);
        #if (function_exists('libxml_disable_entity_loader')) {
            #libxml_disable_entity_loader($loadXmlEntities);
        #}
        // Return generated files
        return $outputArray;
    }

    protected function getSimpleXmlElementAttribute($data)
    {
        if ($data === null) {
            return "";
        }
        $current = @current($data);
        if ($current === false) {
            $stringData = (string)$data;
            if (isset($data[0])) {
                return $data[0];
            } else if ($stringData !== '') {
                return $stringData;
            }
        }
        return $current;
    }

    protected function replaceCharacters($matches)
    {
        return "<$matches[1]>" . str_replace($this->searchCharacters, $this->replaceCharacters, $matches[2]) . "</$matches[3]>";
    }

    protected function addVariablesToXSLT(\XSLTProcessor $xslTemplateObj, $exportArray, $xslTemplateXml)
    {
        if ($this->isRequiredInXslTemplate('$totalitemcount', $xslTemplateXml)) {
            // Total item count
            $xslTemplateObj->setParameter('', 'totalitemcount', $this->getVariableValue('total_item_count', $exportArray));
        }
        if ($this->isRequiredInXslTemplate('$collectioncount', $xslTemplateXml)) {
            // Collection count
            $xslTemplateObj->setParameter('', 'collectioncount', $this->getVariableValue('collection_count', $exportArray));
        }
        if ($this->isRequiredInXslTemplate('$ordercount', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'ordercount', $this->getVariableValue('collection_count', $exportArray)); // Legacy
        }
        // Export ID
        if ($this->isRequiredInXslTemplate('$exportid', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'exportid', $this->getVariableValue('export_id', $exportArray));
        }
        // Date information
        if ($this->isRequiredInXslTemplate('$dateFromTimestamp', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'dateFromTimestamp', $this->getVariableValue('date_from_timestamp', $exportArray));
        }
        if ($this->isRequiredInXslTemplate('$dateToTimestamp', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'dateToTimestamp', $this->getVariableValue('date_to_timestamp', $exportArray));
        }
        // GUID
        if ($this->isRequiredInXslTemplate('$guid', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'guid', $this->getVariableValue('guid', $exportArray));
        }
        // Current timestamp
        if ($this->isRequiredInXslTemplate('$timestamp', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'timestamp', $this->localeDate->scopeTimeStamp());
        }
        // How often was this object exported before by this profile?
        if ($this->isRequiredInXslTemplate('$exportCountForObject', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'exportCountForObject', $this->getVariableValue('export_count_for_object', $exportArray));
        }
        // How many objects have been exported today by this profile?
        if ($this->isRequiredInXslTemplate('$dailyExportCounter', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'dailyExportCounter', $this->getVariableValue('daily_export_counter', $exportArray));
        }
        // How many objects have been exported by this profile? Basically an incrementing counter for each export
        if ($this->isRequiredInXslTemplate('$profileExportCounter', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'profileExportCounter', $this->getVariableValue('profile_export_counter', $exportArray));
        }
        // Max item count: Number of items in the order with the most items: Required for example if you want to output one column per item ordered, and need to output a loop so one column per item can be added
        if ($this->isRequiredInXslTemplate('$maxItemCount', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'maxItemCount', $this->getVariableValue('max_item_count', $exportArray));
        }
        return $this;
    }

    /*
     * Check if the variable is used in the XSL Template and only if yes return true
     */
    protected function isRequiredInXslTemplate($variable, $xslTemplateXml)
    {
        if (strpos($xslTemplateXml, $variable) === FALSE) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * Many old XSL Templates are still using orders/order. Replace with objects/object on the fly.
     */
    protected function preparseXslTemplate($xslTemplate)
    {
        return str_replace(
            [
                '<xsl:for-each select="orders/order">',
                '<xsl:for-each select="customers/customer">',
                '<xsl:for-each select="invoices/invoice">',
                '<xsl:for-each select="shipments/shipment">',
                'custom_options/option'
            ],
            [
                '<xsl:for-each select="objects/object">',
                '<xsl:for-each select="objects/object">',
                '<xsl:for-each select="objects/object">',
                '<xsl:for-each select="objects/object">',
                'custom_options/custom_option'
            ],
            $xslTemplate
        );
    }

    protected function getPdfsForOrderIds($orderIds, $fileType)
    {
        /*if (preg_match("/fooman\_/", $fileType)) { // Valid types: fooman_invoice, fooman_order, fooman_shipment, fooman_creditmemo
            return Mage::getModel('pdfcustomiser/' . str_replace('fooman_', '', $fileType))->renderPdf(null, $orderIds, null, true, null, null)->Output('', 'S');
        }*/
        if ($fileType == 'invoice_pdf') {
            $invoiceCollection = $this->invoiceCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);
            if (!$invoiceCollection->getSize()) {
                return false;
            }
            return $this->pdfInvoice->getPdf($invoiceCollection->getItems())->render();
        }
        if ($fileType == 'packingslip_pdf') {
            $shipmentCollection = $this->shipmentCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);
            if (!$shipmentCollection->getSize()) {
                return false;
            }
            return $this->pdfShipment->getPdf($shipmentCollection->getItems())->render();
        }
        if ($fileType == 'creditmemo_pdf') {
            $creditmemoCollection = $this->creditmemoCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);
            if (!$creditmemoCollection->getSize()) {
                return false;
            }
            return $this->pdfCreditmemo->getPdf($creditmemoCollection->getItems())->render();
        }
        return false;
    }
}