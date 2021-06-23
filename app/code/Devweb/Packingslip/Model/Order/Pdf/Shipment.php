<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Devweb\Packingslip\Model\Order\Pdf;

use Magento\Sales\Model\Order\Pdf\Config;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGenerator;

/**
 * Sales Order Shipment PDF model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Shipment extends \Magento\Sales\Model\Order\Pdf\Shipment
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;
    /**
     * @var BarcodeGeneratorPNG
     */
    protected $generatorPng;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        BarcodeGeneratorPNG $barcodeGeneratorPNG,
        \Magento\Store\Model\App\Emulation $appEmulation,
        array $data = []
    ) {
        $this->appEmulation=$appEmulation;
        $this->_storeManager=$storeManager;
        $this->generatorPng=$barcodeGeneratorPNG;
        parent::__construct($paymentData, $string, $scopeConfig, $filesystem, $pdfConfig, $pdfTotalFactory,
            $pdfItemsFactory, $localeDate, $inlineTranslation, $addressRenderer, $storeManager, $appEmulation, $data);
    }

    /**
     * Draw table header for product items
     *
     * @param  \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 35];

        $lines[0][] = ['text' => __('SKU'), 'feed' => 250, 'align' => 'right'];

        $lines[0][] = ['text' => __('Bin'), 'feed' => 300, 'align' => 'right'];

        $lines[0][] = ['text' => __('Price'), 'feed' => 370, 'align' => 'right'];

        $lines[0][] = ['text' => __('Qty'), 'feed' => 445, 'align' => 'right'];

        $lines[0][] = ['text' => __('Tax'), 'feed' => 495, 'align' => 'right'];

        $lines[0][] = ['text' => __('Subtotal'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document
     *
     * @param \Magento\Sales\Model\Order\Shipment[] $shipments
     * @return \Zend_Pdf
     */

    protected function insertTotals($page, $source)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currencyCode = $storeManager->getStore()->getCurrentCurrencyCode();

        $order = $source->getOrder();

        $items = $order->getAllItems();
        $itemQuantity = 0;

        foreach ($items as $item) {
            if($item->getData('product_type') == "simple") {
                $itemQuantity = $itemQuantity + $item->getQtyOrdered();
            }
        }

        $Subtotal = $order->getSubTotal();
        $ShippingAmount = $order->getShippingAmount();
        $GrandTotal = $order->getGrandTotal();
        $totals = $this->_getTotalsList();

        $lineBlock = ['lines' => [], 'height' => 15];
        $itr = 0;
        foreach ($totals as $total) {
            $total->setOrder($order)->setSource($source);
            if ($total->canDisplay()) {
                $total->setFontSize(10);
                foreach ($total->getTotalsForDisplay() as $totalData) {
                    if($itr == 0) {
                        ++$itr;
                        $lineBlock['lines'][] = [
                            [
                                'text' => $totalData['label'],
                                'feed' => 475,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold',
                            ],
                            [
                                'text' => $currencyCode.number_format($Subtotal,2),
                                'feed' => 565,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold'
                            ]
                        ];
                        $lineBlock['lines'][] = [
                            [
                                'text' => 'Shipment',
                                'feed' => 475,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold',
                            ],
                            [
                                'text' => $currencyCode.number_format($ShippingAmount,2),
                                'feed' => 565,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold'
                            ]
                        ];
                        $lineBlock['lines'][] = [
                            [
                                'text' => 'Total Quantity:',
                                'feed' => 150,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold',
                            ],
                            [
                                'text' => $itemQuantity,
                                'feed' => 160,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold'
                            ],
                        ];
                    }
                    else{
                        $lineBlock['lines'][] = [
                            [
                                'text' => $totalData['label'],
                                'feed' => 475,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold',
                            ],
                            [
                                'text' => $currencyCode.number_format($GrandTotal,2),
                                'feed' => 565,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold'
                            ],
                        ];
                    }
                }
            }
        }

        $this->y -= 20;
        $page = $this->drawLineBlocks($page, [$lineBlock]);
        return $page;
    }

    public function getPdf($invoices = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                    $this->appEmulation->startEnvironmentEmulation(
                    $invoice->getStoreId(),
                    \Magento\Framework\App\Area::AREA_FRONTEND,
                    true
                );
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }
            $page = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Packing Slip # ') . $invoice->getIncrementId());
            /* Add table */
            $this->_drawHeader($page);

            $docHeader = $this->getDocHeaderCoordinates();
            $image = $this->_generateBarcode($order->getIncrementId());
            $width = '130';
            $height = '50';

            $page->drawImage($image, $docHeader[2] - $width, $docHeader[1] -$height, $docHeader[2], $docHeader[1]);
            /* Add body */
            $total = 0;
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                if(!$item->getOrderItem()->getData('qty_shipped') >= (float)1.0000) {
                    continue;
                }
                $total += ($item->getPrice() - $item->getOrderItem()->getDiscountAmount())*$item->getQty();

                $item['subtotal'] = $total;

                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                $this->appEmulation->stopEnvironmentEmulation();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return \Zend_Pdf_Page
     */
    public function newPage(array $settings = [])
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }

    protected function _generateBarcode($orderIncrementId) {
      $barcodeContent =   $this->generatorPng->getBarcode($orderIncrementId, BarcodeGenerator::TYPE_CODE_128);
        $imagick = new \Imagick();
        $imagick->readImageBlob($barcodeContent);

        // image with text code
        $imagick->newImage($imagick->getImageWidth(), 20, 'none');
        $draw = new \ImagickDraw();
        $draw->setFillColor('black');
        $draw->setFont('Bookman-Demi');
        $draw->setFontSize(15);
        $imagick->annotateImage($draw, 45, 15, 0, $orderIncrementId);

        // Vertical concatenation (barcode and textcode)
        $imagick->resetIterator();
        $imagick = $imagick->appendImages(true);

        // format and generate base64 image
        $imagick->setImageFormat("png");
        $barCodeImg = $imagick->getImageBlob();
        $image = new \Zend_Pdf_Resource_Image_Png('data:image/png;base64,'.base64_encode($barCodeImg));
       return $image;
    }

}
