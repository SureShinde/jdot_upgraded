<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */

namespace Infomodus\Dhllabel\Helper;

use DVDoug\BoxPacker\Packer;
use Infomodus\Dhllabel\Model\Config\Options;
use Infomodus\Dhllabel\Model\Packer\TestBox as PackerBox;
use Infomodus\Dhllabel\Model\Packer\TestItem as PackerItem;
use Magento\Framework\Module\Dir;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;

class Handy extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $_context;
    public $_objectManager;
    public $_conf;
    public $_registry;
    public $order = null;
    public $shipment;
    public $shipment_id = null;
    public $type;
    public $type2;
    public $paymentmethod;
    public $shipmentTotalPrice;
    public $shippingAddress;
    public $defConfParams;
    public $defPackageParams = [];
    public $shipByDhl;
    public $shipByDhlCode;
    public $dhlAccounts;
    public $label = [];
    public $label2 = [];
    public $storeId;
    public $error;
    public $totalWeight;

    protected $shipmentFactory;
    protected $shipmentSender;
    protected $shipmentLoaderFactory;

    public $rates_tax;

    protected $messageManager;
    protected $configReader;
    private $pdfA4Height;
    private $pdfCurrentHeight;
    private $pdf;
    private $pdfCurrentPage;

    protected $_currencyFactory;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    public $orderRepository;
    /**
     * @var \Magento\Sales\Model\Order\ShipmentRepository
     */
    protected $shipmentRepository;
    /**
     * @var \Magento\Sales\Model\Order\CreditmemoRepository
     */
    protected $creditmemoRepository;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    protected $accountCollectionFactory;
    protected $conformity;
    protected $dhl;
    protected $country;
    protected $account;
    protected $itemsFactory;
    protected $trackFactory;
    protected $shipmentServiceFactory;

    /**
     * @var \Infomodus\Upslabel\Model\Config\Defaultaddress
     */
    private $addresses;
    private $defaultdimensionsset;
    private $logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Infomodus\Dhllabel\Helper\Config $config,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoaderFactory $shipmentLoaderFactory,
        ShipmentSender $shipmentSender,
        ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Sales\Model\Order\CreditmemoRepository $creditmemoRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Module\Dir\Reader $configReader,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Infomodus\Dhllabel\Model\Config\Defaultaddress $addresses,
        \Infomodus\Dhllabel\Model\Config\Defaultdimensionsset $defaultdimensionsset,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Infomodus\Dhllabel\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory,
        \Infomodus\Dhllabel\Model\ConformityFactory $conformity,
        \Infomodus\Dhllabel\Model\Dhl $dhl,
        \Magento\Directory\Model\Country $country,
        \Infomodus\Dhllabel\Model\AccountFactory $account,
        \Infomodus\Dhllabel\Model\ItemsFactory $itemsFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Sales\Model\Service\ShipmentServiceFactory $shipmentServiceFactory
    )
    {
        $this->shipmentSender = $shipmentSender;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentLoaderFactory = $shipmentLoaderFactory;
        $this->_registry = $registry;
        parent::__construct($context);
        $this->_context = $context;
        $this->_objectManager = $objectManager;
        $this->_conf = $config;
        $this->messageManager = $messageManager;
        $this->configReader = $configReader;
        $this->_currencyFactory = $currencyFactory;
        $this->orderRepository = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->productRepository = $productRepository;
        $this->defaultdimensionsset = $defaultdimensionsset;
        $this->addresses = $addresses;
        $this->logger = $context->getLogger();
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->conformity = $conformity;
        $this->dhl = $dhl;
        $this->country = $country;
        $this->account = $account;
        $this->itemsFactory = $itemsFactory;
        $this->trackFactory = $trackFactory;
        $this->shipmentServiceFactory = $shipmentServiceFactory;
    }


    public function intermediate($order, $type, $shipment_id = null)
    {
        $this->defConfParams = [];
        $this->shipment_id = $shipment_id;
        unset($shipment_id);
        if ($order !== null) {
            if (!is_numeric($order)) {
                $this->order = $order;
            } else {
                $this->order = $this->orderRepository->get($order);
            }
        } elseif ($this->shipment_id !== null) {
            if ($type !== 'refund') {
                $this->order = $this->shipmentRepository->get($this->shipment_id)->getOrder();
            } else {
                $this->order = $this->shipment = $this->creditmemoRepository->get($this->shipment_id)->getOrder();
            }
        }

        unset($order);
        $this->type = $type;
        unset($type);

        $this->storeId = $this->order->getStoreId();
        $this->paymentmethod = "";
        if (is_object($this->order->getPayment())) {
            $this->paymentmethod = $this->order->getPayment()->getData();
            $this->paymentmethod = $this->paymentmethod['method'];
        }

        $this->shippingAddress = $this->order->getShippingAddress();
        if (!$this->shippingAddress) {
            $this->shippingAddress = $this->order->getBillingAddress();
        }

        if ($this->shipment_id !== null) {
            if ($this->type != 'refund' || $this->order->hasCreditmemos() == 0) {
                $this->shipment = $this->shipmentRepository->get($this->shipment_id);
            } else {
                $creditmemo = $this->creditmemoRepository->get($this->shipment_id);
                if ($creditmemo && $creditmemo->getOrderId() == $this->order->getId()) {
                    $this->shipment = $creditmemo;
                } else {
                    $this->shipment = $this->shipmentRepository->get($this->shipment_id);
                }
            }

            $shipmentAllItems = $this->shipment->getAllItems();
        } else {
            $shipmentAllItems = $this->order->getAllVisibleItems();
        }

        $totalPrice = 0;
        $this->totalWeight = 0;
        $totalShipmentQty = 0;
        $this->sku = [];
        $pi = 1;
        $shipmentDescription = [];
        $itemDeclaredTotalPrice = 0;
        $this->defConfParams['invoice_product'] = [];
        $productRepository = $this->productRepository;

        $this->allowedCurrencies = $this->_currencyFactory->create()->getConfigAllowCurrencies();
        $baseCurrencyCode = $this->_conf->getStoreConfig('currency/options/base', $this->storeId);
        $baseOrderBaseCurrencyCode = $this->order->getBaseCurrencyCode();
        $responseCurrencyCode = $this->_conf->getStoreConfig('dhllabel/ratepayment/currencycode', $this->storeId);
        $currencyKoef = 1;

        if ($responseCurrencyCode != $baseOrderBaseCurrencyCode && $baseCurrencyCode == $baseOrderBaseCurrencyCode) {
            if (in_array($responseCurrencyCode, $this->allowedCurrencies)) {
                $currencyKoef = $this->_getBaseCurrencyKoef($baseCurrencyCode, $responseCurrencyCode);
            }
        }

        foreach ($shipmentAllItems as $item) {
            if (!$item->isDeleted() && !$item->getParentItemId()) {
                if ($item->getOrderItemId()) {
                    $item = $this->order->getItemById($item->getOrderItemId());
                }

                if (!$item) {
                    continue;
                }

                $originProductObject = $productRepository->getById($item->getProductId(), false, $this->storeId);
                if (!$originProductObject->getIsVirtual()) {
                    $originProduct = $originProductObject->getData();
                    $itemData = $item->getData();
                    if (empty($itemData['weight'])) {
                        $itemData['weight'] = 0.1;
                    }
                    if (empty($itemData['sku'])) {
                        $itemData['sku'] = '';
                    }
                    $this->sku[] = $itemData['sku'];
                    if (!isset($itemData['qty'])) {
                        $itemData['qty'] = $itemData['qty_ordered'];
                    }

                    if ($this->_conf->getStoreConfig('dhllabel/shipping/shipmentdescription', $this->storeId) == 4) {
                        $shipmentDescription[] = $itemData['name'];
                    }

                    $itemPrice = ($item->getBasePrice() - $item->getBaseDiscountAmount() / $itemData['qty']) * $currencyKoef;
                    $totalPrice += $itemPrice * $itemData['qty'];
                    $this->totalWeight += $itemData['weight'] * $itemData['qty'];
                    $totalShipmentQty += $itemData['qty'];

                    $originProductObjectChild = $originProductObject;
                    if($item->getProductType() == 'configurable' && $item->getHasChildren()){
                        foreach ($item->getChildrenItems() as $child){
                            $originProductObjectChild = $productRepository->getById($child->getProductId(), false, $this->storeId);
                            break;
                        }
                    }

                    if ($this->_conf->getStoreConfig('dhllabel/paperless/attribute_product_name_1', $this->storeId) != '') {
                        $itemName = $this->getAttributeContent($originProductObjectChild, $this->_conf->getStoreConfig('dhllabel/paperless/attribute_product_name_1', $this->storeId));
                    } else {
                        $originProductChild = $originProductObjectChild->getData();
                        $itemName = isset($originProductChild['declared_name'])
                        && strlen(trim($originProductChild['declared_name'])) > 0
                            ? $originProductChild['declared_name'] : $itemData['name'];
                    }

                    $itemDeclaredTotalPrice += $itemPrice * $itemData['qty'];
                    $commoditycode = (strlen($this->_conf->getStoreConfig('dhllabel/paperless/commodity_attribute', $this->storeId)) > 0 && isset($originProduct[$this->_conf->getStoreConfig('dhllabel/paperless/commodity_attribute', $this->storeId)])) ? $originProduct[$this->_conf->getStoreConfig('dhllabel/paperless/commodity_attribute', $this->storeId)] : '';
                    $dgAttributeContentId = (strlen($this->_conf->getStoreConfig('dhllabel/shipping/dg_attribute_content_id', $this->storeId)) > 0 && isset($originProduct[$this->_conf->getStoreConfig('dhllabel/shipping/dg_attribute_content_id', $this->storeId)])) ? $originProduct[$this->_conf->getStoreConfig('dhllabel/shipping/dg_attribute_content_id', $this->storeId)] : '';
                    $dgAttributeLabel = (strlen($this->_conf->getStoreConfig('dhllabel/shipping/dg_attribute_label', $this->storeId)) > 0 && isset($originProduct[$this->_conf->getStoreConfig('dhllabel/shipping/dg_attribute_label', $this->storeId)])) ? $originProduct[$this->_conf->getStoreConfig('dhllabel/shipping/dg_attribute_label', $this->storeId)] : '';
                    $dgAttributeUNCode = (strlen($this->_conf->getStoreConfig('dhllabel/shipping/dg_attribute_uncode', $this->storeId)) > 0 && isset($originProduct[$this->_conf->getStoreConfig('dhllabel/shipping/dg_attribute_uncode', $this->storeId)])) ? $originProduct[$this->_conf->getStoreConfig('dhllabel/shipping/dg_attribute_uncode', $this->storeId)] : '';
                    $this->defConfParams['invoice_product'][] = [
                        'enabled' => 1,
                        'qty' => $itemData['qty'],
                        'name' => $itemName,
                        'weight' => $itemData['weight'],
                        'price' => round($itemPrice, 2),
                        'sku' => $itemData['sku'],
                        'id' => $itemData['product_id'],
                        'commodity_code' => $commoditycode,
                        'dangerous_goods' => $this->_conf->getStoreConfig('dhllabel/shipping/dangerous_goods', $this->storeId),
                        'dg_attribute_content_id' => $dgAttributeContentId,
                        'dg_attribute_label' => $dgAttributeLabel,
                        'dg_attribute_uncode' => $dgAttributeUNCode,
                        'country_of_manufacture' => !empty($originProduct['country_of_manufacture']) ? $originProduct['country_of_manufacture'] : null,
                        'country_of_manufacture_name' => !empty($originProduct['country_of_manufacture']) ? $this->country->loadByCode($originProduct['country_of_manufacture'])->getName() : null,
                    ];
                    $pi++;
                }
            }
        }

        $this->sku = implode(",", $this->sku);
        $totalQty = 0;
        foreach ($this->order->getAllVisibleItems() as $item) {
            if (!$item->getProduct()->getIsVirtual()) {
                $itemData = $item->getData();
                if (!isset($itemData['qty'])) {
                    $itemData['qty'] = $itemData['qty_ordered'];
                }
                $totalQty += $itemData['qty'];
            }
        }

        $this->dhlAccounts = ["S" => "Shipper"];
        $this->dhlAccountsDuty = ["S" => "Shipper", "R" => "Recipient"];
        $dhlAcctModel = $this->accountCollectionFactory->create()->load();
        foreach ($dhlAcctModel as $u1) {
            $this->dhlAccounts[$u1->getId()] = $u1->getCompanyname();
            $this->dhlAccountsDuty[$u1->getId()] = $u1->getCompanyname();
        }

        if (count($shipmentAllItems) != count($this->order->getAllVisibleItems())) {
            $this->shipmentTotalPrice = $totalPrice;
        } else {
            $this->shipmentTotalPrice = ($this->order->getBaseGrandTotal() - $this->order->getBaseShippingAmount()) * $currencyKoef;
        }

        $this->defConfParams['dhlaccount'] = $this->_conf
            ->getStoreConfig('dhllabel/ratepayment/third_party', $this->storeId);

        $this->defConfParams['dhlaccount_duty'] = $this->_conf
            ->getStoreConfig('dhllabel/ratepayment/third_party_of_duty', $this->storeId);


        $ship_method = $this->order->getShippingMethod();
        $address = $this->addresses->getAddressesById($this->_conf->getStoreConfig('dhllabel/shipping/defaultshipper', $this->storeId));
        $shippingInternational = ($this->shippingAddress->getCountryId() == $address->getCountry()) ? 0 : 1;

        $shippingIsEU = ($this->shippingAddress->getCountryId() != $address->getCountry()
            && strpos($this->_conf->getStoreConfig('general/country/eu_countries', $this->storeId), $address->getCountry()) !== false
            && strpos($this->_conf->getStoreConfig('general/country/eu_countries', $this->storeId), $this->shippingAddress->getCountryId()) !== false) ? 1 : 0;

        $this->shipByDhl = preg_replace("/^dhl_.{1,4}$/", 'dhl', $ship_method);

        if ($this->shipByDhl == 'dhl') {
            $this->shipByDhlCode = preg_replace("/^dhl_(.{1,4})$/", '$1', $ship_method);
            $this->defConfParams['serviceCode'] = $this->shipByDhlCode;
        } elseif ($this->shipByDhl = preg_replace("/^caship_.{1,100}$/", 'caship', $ship_method) == 'caship') {
            $this->shipByDhl = 'dhl';
            $this->shipByDhlCode = explode("_", $ship_method);
            $apModel = $this->_objectManager->get('Infomodus\Caship\Model\Items')->load($this->shipByDhlCode[1]);
            if ($apModel && ($apModel->getCompanyType() == 'dhl' || $apModel->getCompanyType() == 'dhlinfomodus')) {
                $this->shipByDhlCode = $apModel->getDhlmethodId();
                $this->defConfParams['serviceCode'] = $this->shipByDhlCode;
            }
        } elseif ($this->_conf->getStoreConfig('dhllabel/shipping/shipping_method_native', $this->storeId) == 1) {
            $modelConformity = $this->conformity->create()->getCollection()
                ->addFieldToFilter('method_id', $ship_method)->addFieldToFilter('store_id',
                    $this->storeId ? $this->storeId : 1)
                ->getSelect()->where('CONCAT(",", country_ids, ",") LIKE "%,' .
                    $this->shippingAddress->getCountryId() . ',%"')->query()->fetch();
            if ($modelConformity && count($modelConformity) > 0) {
                $this->defConfParams['serviceCode'] = $modelConformity["dhlmethod_id"];
            }
        }

        if (!isset($this->defConfParams['serviceCode'])) {
            $this->defConfParams['serviceCode'] = $shippingInternational == 0 ?
                $this->_conf->getStoreConfig('dhllabel/shipping/defaultshipmentmethod', $this->storeId) :
                ($shippingIsEU == 0 ? $this->_conf->getStoreConfig('dhllabel/shipping/defaultshipmentmethodworld', $this->storeId) : $this->_conf->getStoreConfig('dhllabel/shipping/defaultshipmentmethod_eu', $this->storeId));
        }

        if ($this->totalWeight <= 0) {
            $this->totalWeight = (float)str_replace(',', '.',
                $this->_conf->getStoreConfig('dhllabel/weightdimension/defweigth', $this->storeId));
            if ($this->totalWeight == '' || $this->totalWeight <= 0) {
                $this->messageManager->addErrorMessage("Some of the products are missing their weight information. Please fill the weight for all products or enter a default value from the \"Weight and Dimensions\" section of the DHL module configuration.");
            }
        }

        $attributeCodeWidth = $this->_conf->getStoreConfig('dhllabel/packaging/multipackes_attribute_width', $this->storeId) ?
            $this->_conf->getStoreConfig('dhllabel/packaging/multipackes_attribute_width', $this->storeId) : 'width';
        $attributeCodeHeight = $this->_conf->getStoreConfig('dhllabel/packaging/multipackes_attribute_height', $this->storeId) ?
            $this->_conf->getStoreConfig('dhllabel/packaging/multipackes_attribute_height', $this->storeId) : 'height';
        $attributeCodeLength = $this->_conf->getStoreConfig('dhllabel/packaging/multipackes_attribute_length', $this->storeId) ?
            $this->_conf->getStoreConfig('dhllabel/packaging/multipackes_attribute_length', $this->storeId) : 'length';
        $prePackedAttribute = $this->_conf->getStoreConfig('dhllabel/packaging/product_without_box', $this->storeId);

        /* Multi package */
        $dimensionSets = $this->defaultdimensionsset->toOptionObjects();

        if ($this->type == 'shipment'
            && $this->_conf->getStoreConfig('dhllabel/packaging/frontend_multipackes_enable',
                $this->storeId) == 1
        ) {
            $i = 0;
            $defParArr_1 = [];
            foreach ($shipmentAllItems as $item) {
                if (!$item->isDeleted() && !$item->getParentItemId()) {
                    $itemData = $item->getData();
                    if (!isset($itemData['qty'])) {
                        $itemData['qty'] = $itemData['qty_ordered'];
                    }

                    if (!isset($itemData['weight'])) {
                        foreach ($this->order->getAllVisibleItems() as $w) {
                            if ($w->getProductId() == $itemData["product_id"]) {
                                $itemData['weight'] = $w->getWeight();
                            }
                        }
                    }

                    $originProductObject = $this->productRepository->getById($itemData['product_id']);
                    if (!$originProductObject->getIsVirtual()) {
                        $myproduct = $originProductObject->getData();
                        for ($ik = 0; $ik < $itemData['qty']; $ik++) {
                            $is_attribute = 0;
                            if ($this->_conf
                                    ->getStoreConfig('dhllabel/packaging/packages_by_attribute_enable',
                                        $this->storeId) == 1) {
                                if (isset($myproduct[$this->_conf
                                        ->getStoreConfig('dhllabel/packaging/packages_by_attribute_code',
                                            $this->storeId)])) {
                                    $attribute = explode(";",
                                        trim($myproduct[$this->_conf
                                            ->getStoreConfig(
                                                'dhllabel/packaging/packages_by_attribute_code',
                                                $this->storeId)], ";"));
                                    if (count($attribute) > 1) {
                                        $rvaPrice = $item->getBasePrice() * $currencyKoef;
                                        foreach ($attribute as $v) {
                                            $itemData['weight'] = $v;
                                            $itemData['price'] = round($rvaPrice / count($attribute), 2);
                                            $defParArr_1[$i] = $this->setPackageDefParams($itemData);
                                            $i++;
                                        }
                                        $is_attribute = 1;
                                    }
                                }
                            }

                            if ($is_attribute !== 1) {
                                $countProductInBox = 0;
                                try {
                                    $packer = new Packer();
                                    $myproduct = $this->productRepository->getById($itemData['product_id'])->getData();

                                    if ($item->getWeight()) {
                                        $myproduct['weight'] = $item->getWeight();

                                        $myproduct = $this->getProductSizes(
                                            $item,
                                            $itemData,
                                            $myproduct,
                                            $packer,
                                            $attributeCodeWidth,
                                            $attributeCodeHeight,
                                            $attributeCodeLength,
                                            $currencyKoef
                                        );
                                        if ($myproduct === false) {
                                            $this->messageManager->addErrorMessage("Product " . $item->getName() . " does not have width or height or length");
                                            break;
                                        } else {
                                            $countProductInBox++;
                                        }

                                        if ($countProductInBox > 0) {
                                            $packer->addBox(new PackerBox(
                                                'def_box',
                                                1000000,
                                                1000000,
                                                1000000,
                                                0,
                                                1000000,
                                                1000000,
                                                1000000,
                                                550000
                                            ));

                                            $packedBoxes = $packer->pack();
                                            if (count($packedBoxes) > 0) {
                                                foreach ($packedBoxes as $packedBox) {
                                                    $itemDataTwo = [];
                                                    $boxType = $packedBox->getBox();
                                                    $itemDataTwo['width'] = round($packedBox->getUsedWidth() / 1000, 2);
                                                    $itemDataTwo['length'] = round($packedBox->getUsedLength() / 1000, 2);
                                                    $itemDataTwo['height'] = round($packedBox->getUsedDepth() / 1000, 2);
                                                    $itemDataTwo['weight'] = $packedBox->getWeight() / 1000;
                                                    $itemDataTwo['empty_weight'] = $boxType->getEmptyWeight() / 1000;
                                                    $itemsInTheBox = $packedBox->getItems();
                                                    $itemDataTwo['price'] = 0;
                                                    foreach ($itemsInTheBox as $itemBox) {
                                                        $itemDataTwo['price'] += $itemBox->getItem()->getDescription();
                                                    }

                                                    $defParArr_1[$i] = $this->setPackageDefParams($itemDataTwo, $this->storeId, $this->type);
                                                    $i++;
                                                }
                                            }

                                        }
                                    }
                                } catch (\DVDoug\BoxPacker\ItemTooLargeException $e) {
                                    $this->logger->info($e->getMessage());
                                }
                            }
                        }
                    }
                }
            }
            $this->defPackageParams = $defParArr_1;
        } else {
            $this->defPackageParams = [];
            $i = 0;
            $rvaShipmentTotalPrice = $this->shipmentTotalPrice;
            if ($this->_conf->getStoreConfig('dhllabel/packaging/packages_by_attribute_enable',
                    $this->storeId) == 1 && $this->type == 'shipment') {
                foreach ($shipmentAllItems as $item) {
                    if (!$item->isDeleted() && !$item->getParentItemId()) {
                        $itemData = $item->getData();
                        if (!isset($itemData['qty'])) {
                            $itemData['qty'] = $itemData['qty_ordered'];
                        }

                        if (!isset($itemData['weight'])) {
                            foreach ($this->order->getAllVisibleItems() as $w) {
                                if ($w->getProductId() == $itemData["product_id"]) {
                                    $itemData['weight'] = $w->getWeight();
                                }
                            }
                        }

                        $itemData2 = $itemData;
                        $originProductObject = $this->productRepository->getById($itemData['product_id']);
                        if (!$originProductObject->getIsVirtual()) {
                            $myproduct = $originProductObject->getData();
                            for ($ik = 0; $ik < $itemData['qty']; $ik++) {
                                if (isset($myproduct[$this->_conf
                                        ->getStoreConfig('dhllabel/packaging/packages_by_attribute_code',
                                            $this->storeId)])) {
                                    $attribute = explode(";", trim($myproduct[$this->_conf
                                        ->getStoreConfig('dhllabel/packaging/packages_by_attribute_code',
                                            $this->storeId)], ";"));
                                    if (count($attribute) > 1) {
                                        foreach ($attribute as $v) {
                                            $this->totalWeight = $this->totalWeight - $itemData2['weight'];
                                            $itemData['price'] = round($item->getBasePrice() * $currencyKoef / count($attribute), 2);
                                            $itemData['weight'] = $v;
                                            $this->defPackageParams[$i] = $this->setPackageDefParams($itemData);
                                            $i++;
                                        }

                                        $rvaShipmentTotalPrice = $rvaShipmentTotalPrice - $itemData2['price'];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($this->totalWeight > 0) {
                $countProductInBox = 0;
                if ($this->type == 'shipment' || $this->type == 'invert') {
                    if (count($dimensionSets) > 0) {
                        try {
                            $packer = new Packer();
                            foreach ($shipmentAllItems as $item) {
                                if (!$item->isDeleted() && !$item->getParentItemId()) {
                                    $itemData = $item->getData();
                                    if (!isset($itemData['qty'])) {
                                        $itemData['qty'] = $itemData['qty_ordered'];
                                    }

                                    $originProductObject = $this->productRepository->getById($itemData['product_id']);
                                    if (!$originProductObject->getIsVirtual()) {
                                        $myproduct = $originProductObject->getData();

                                        if ($item->getWeight() && (!isset($myproduct['weight']) || !$myproduct['weight'])) {
                                            $myproduct['weight'] = $item->getWeight();
                                        }
                                        $isPrePackedExist = false;
                                        for ($ik = 0; $ik < $itemData['qty']; $ik++) {
                                            if (!empty($prePackedAttribute) && !empty($myproduct[$prePackedAttribute])) {
                                                $itemData['width'] = $myproduct[$attributeCodeWidth];
                                                $itemData['length'] = $myproduct[$attributeCodeLength];
                                                $itemData['height'] = $myproduct[$attributeCodeHeight];
                                                $this->defPackageParams[$i] = $this->setPackageDefParams($itemData);
                                                $isPrePackedExist = true;
                                                $i++;
                                                continue;
                                            }
                                            $myproduct = $this->getProductSizes(
                                                $item,
                                                $itemData,
                                                $myproduct,
                                                $packer,
                                                $attributeCodeWidth,
                                                $attributeCodeHeight,
                                                $attributeCodeLength,
                                                $currencyKoef
                                            );
                                            if ($myproduct === false) {
                                                $countProductInBox = 0;
                                                $this->messageManager->addErrorMessage("Product " . $item->getName() . " does not have width or height or length");
                                                break;
                                            } else {
                                                $countProductInBox++;
                                            }
                                        }

                                        if ($countProductInBox == 0 && $isPrePackedExist === false) {
                                            break;
                                        }
                                    }
                                }
                            }

                            if ($countProductInBox > 0) {
                                foreach ($dimensionSets as $v) {
                                    if (!empty($v)) {
                                        $packer->addBox(new PackerBox(
                                            $v->getId(),
                                            $v->getOuterWidth() * 1000,
                                            $v->getOuterLengths() * 1000,
                                            $v->getOuterHeight() * 1000,
                                            $v->getEmptyWeight() * 1000,
                                            $v->getWidth() * 1000,
                                            $v->getLengths() * 1000,
                                            $v->getHeight() * 1000,
                                            $v->getMaxWeight() * 1000
                                        ));
                                    }
                                }
                                $packedBoxes = $packer->pack();
                                if (count($packedBoxes) > 0) {
                                    foreach ($packedBoxes as $packedBox) {
                                        $itemData = [];
                                        $boxType = $packedBox->getBox();
                                        $itemData['width'] = round($boxType->getOuterWidth() / 1000, 2);
                                        $itemData['length'] = round($boxType->getOuterLength() / 1000, 2);
                                        $itemData['height'] = round($boxType->getOuterDepth() / 1000, 2);
                                        $itemData['weight'] = $packedBox->getWeight() / 1000;
                                        $itemData['empty_weight'] = $boxType->getEmptyWeight() / 1000;
                                        $itemsInTheBox = $packedBox->getItems();
                                        $itemData['price'] = 0;
                                        foreach ($itemsInTheBox as $itemBox) {
                                            $itemData['price'] += $itemBox->getItem()->getDescription();
                                        }

                                        $this->defPackageParams[$i] = $this->setPackageDefParams($itemData, $this->storeId, $this->type);
                                        $i++;
                                    }
                                } else {
                                    $countProductInBox = 0;
                                }
                            }
                        } catch (\DVDoug\BoxPacker\ItemTooLargeException $e) {
                            $countProductInBox = 0;
                            $this->logger->info($e->getMessage());
                        }
                    }
                }

                if ($countProductInBox == 0) {
                    $this->defPackageParams[$i] = $this->setPackageDefParams(null);
                }
            }
        }

        $this->defConfParams['shipper_no'] = $this->_conf->getStoreConfig('dhllabel/shipping/defaultshipper',
            $this->storeId);
        $this->defConfParams['testing'] = $this->_conf->getStoreConfig('dhllabel/testmode/testing', $this->storeId);
        $this->defConfParams['addtrack'] = $this->_conf->getStoreConfig('dhllabel/shipping/addtrack', $this->storeId);
        if ($this->_conf->getStoreConfig('dhllabel/shipping/shipmentdescription', $this->storeId) == 4) {
            $this->defConfParams['shipmentdescription'] = implode(', ', $shipmentDescription);
        } elseif ($this->_conf->getStoreConfig('dhllabel/shipping/shipmentdescription', $this->storeId) != 5) {
            $this->defConfParams['shipmentdescription'] = $this->_conf
                ->getStoreConfig('dhllabel/shipping/shipmentdescription', $this->storeId) == 1 ?
                ($this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname() . ' '
                    . __('Order Id') . ': ' . $this->order->getIncrementId()) :
                ($this->_conf->getStoreConfig('dhllabel/shipping/shipmentdescription', $this->storeId) == 2 ?
                    $this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname() :
                    ($this->_conf->getStoreConfig('dhllabel/shipping/shipmentdescription', $this->storeId) !== '' ?
                        __('Order Id') . ': ' . $this->order->getIncrementId() : ''));
        } else {
            $this->defConfParams['shipmentdescription'] = str_replace(["#order_id#", "#customer_name#"],
                [$this->order->getIncrementId(), $this->shippingAddress->getFirstname() . ' '
                    . $this->shippingAddress->getLastname()],
                $this->_conf->getStoreConfig('dhllabel/shipping/shipmentdescription_custom', $this->storeId));
        }

        $this->defConfParams['currencycode'] = $this->_conf->getStoreConfig('dhllabel/ratepayment/currencycode',
            $this->storeId);
        $this->defConfParams['cod'] = $this->_conf->getStoreConfig('dhllabel/ratepayment/cod', $this->storeId) == 1 ?
            1 : (($this->paymentmethod == 'cashondelivery' || $this->paymentmethod == 'phoenix_cashondelivery') ?
                1 : 0);
        $this->defConfParams['codmonetaryvalue'] = $this->shipmentTotalPrice;
        $this->defConfParams['default_return'] = ($this->_conf->getStoreConfig('dhllabel/return/default_return', $this->storeId) == 0 || $this->_conf->getStoreConfig('dhllabel/return/default_return_amount', $this->storeId) > $this->shipmentTotalPrice) ? 0 : 1;
        $this->defConfParams['default_return_servicecode'] = $this->_conf->getStoreConfig('dhllabel/return/default_return_method', $this->storeId);
        $this->defConfParams['qvn'] = $this->_conf->getStoreConfig('dhllabel/quantum/qvn', $this->storeId);
        $this->defConfParams['qvn_message'] = $this->_conf->getStoreConfig('dhllabel/quantum/qvn_message', $this->storeId);
        $this->defConfParams['qvn_email_shipper'] = $this->_conf->getStoreConfig('dhllabel/quantum/qvn_email_shipper', $this->storeId);
        $this->defConfParams['qvn_email_shipto'] = $this->shippingAddress->getEmail();
        $this->defConfParams['weightunits'] = $this->_conf->getStoreConfig('dhllabel/weightdimension/weightunits', $this->storeId);
        $this->defConfParams['includedimensions'] = $this->_conf->getStoreConfig('dhllabel/weightdimension/includedimensions', $this->storeId);
        $this->defConfParams['unitofmeasurement'] = $this->_conf->getStoreConfig('dhllabel/weightdimension/unitofmeasurement', $this->storeId);
        $this->defConfParams['residentialaddress'] = strlen($this->shippingAddress->getCompany()) > 0 ? 0 : 1;
        $this->defConfParams['shiptocompanyname'] = strlen($this->shippingAddress->getCompany()) > 0 ? $this->shippingAddress->getCompany() : $this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname();
        $this->defConfParams['shiptoattentionname'] = $this->shippingAddress->getFirstname() . ' ' . $this->shippingAddress->getLastname();
        $this->defConfParams['shiptophonenumber'] = $this->_conf->escapePhone($this->shippingAddress->getTelephone());
        $addressLine1 = $this->shippingAddress->getStreet();
        $this->defConfParams['shiptoaddressline1'] = is_array($addressLine1) && array_key_exists(0, $addressLine1) ? $addressLine1[0] : $addressLine1;
        $this->defConfParams['shiptoaddressline2'] = (is_array($addressLine1) && isset($addressLine1[1])) ? $addressLine1[1] : '';
        $this->defConfParams['shiptoaddressline3'] = (is_array($addressLine1) && isset($addressLine1[2])) ? $addressLine1[2] : '';
        $this->defConfParams['shiptocity'] = $this->shippingAddress->getCity();
        $this->defConfParams['shiptostateprovincecode'] = $this->shippingAddress->getRegion();
        $this->defConfParams['shiptocountrycode'] = $this->shippingAddress->getCountryId();
        $this->defConfParams['shiptovat'] = $this->shippingAddress->getVatId();
        if ($this->shippingAddress->getCountryId() == 'JP') {
            $this->defConfParams['shiptopostalcode'] = str_replace("-", "", $this->shippingAddress->getPostcode());
            $this->defConfParams['shiptopostalcode'] = substr($this->defConfParams['shiptopostalcode'], 0, 3) . '-' . substr($this->defConfParams['shiptopostalcode'], 3);
        } else {
            $this->defConfParams['shiptopostalcode'] = $this->shippingAddress->getPostcode();
        }

        $this->defConfParams['packagingtypecode'] = $this->_conf->getStoreConfig('dhllabel/packaging/packagingtypecode', $this->storeId);
        $this->defConfParams['reference_id'] = $this->_conf->getStoreConfig('dhllabel/shipping/reference_id', $this->storeId) == 'order' ? $this->order->getIncrementId() : '';
        $this->defConfParams['declared_value'] = round($this->shipmentTotalPrice, 2);
        $this->defConfParams['insured_monetary_value'] = round($this->shipmentTotalPrice, 2);
        $this->defConfParams['terms_of_trade'] = $this->_conf->getStoreConfig('dhllabel/paperless/terms_of_trade', $this->storeId);
        $this->defConfParams['place_of_incoterm'] = $this->_conf->getStoreConfig('dhllabel/paperless/place_of_incoterm', $this->storeId);
        $this->defConfParams['invoice_type'] = $this->_conf->getStoreConfig('dhllabel/paperless/invoice_type', $this->storeId);
        $this->defConfParams['eori_number'] = $this->_conf->getStoreConfig('dhllabel/ratepayment/eori_number', $this->storeId);
        $this->defConfParams['shipper_vat_number'] = $this->_conf->getStoreConfig('dhllabel/ratepayment/vat_number_norway', $this->storeId);

        if ($this->defConfParams['shiptocountrycode'] != $this->_conf->getStoreConfig('dhllabel/address_' . $this->defConfParams['shipper_no'] . '/countrycode', $this->storeId) && $itemDeclaredTotalPrice > 0) {
            $this->defConfParams['declared_value'] = $itemDeclaredTotalPrice;
        }

        if ($this->defConfParams['shiptocountrycode'] != $this->_conf->getStoreConfig('dhllabel/address_' . $this->defConfParams['shipper_no'] . '/countrycode', $this->storeId)) {
            $this->defConfParams['invoice_declared_value'] = $totalPrice;
        }

        $lbl = $this->dhl;
        $lbl = $this->setParams($lbl, $this->defConfParams, $this->defPackageParams);
        $codes = [];

        $prices = $lbl->getShipPrice(false);
        if ($prices !== false && is_array($prices) && count($prices) > 0) {
            foreach ($prices as $k => $price) {
                $codes['global'][] = $price->getProductGlobalCode();
                $codes['local'][] = $price->getProductLocalCode();
                $codes['price'][$price->getProductGlobalCode()] = $price->getTotalAmount();
                $codes['remote'][$price->getProductGlobalCode()] = $price->getQtdShpExChrg();
            }
        }

        $prices = $lbl->getShipPrice(true);
        if ($prices !== false && is_array($prices) && count($prices) > 0) {
            foreach ($prices as $k => $price) {
                $codes['global'][] = $price->getProductGlobalCode();
                $codes['local'][] = $price->getProductLocalCode();
                $codes['price'][$price->getProductGlobalCode()] = $price->getTotalAmount();
                $codes['remote'][$price->getProductGlobalCode()] = $price->getQtdShpExChrg();
            }
        }

        $this->defConfParams['shipping_methods'] = json_encode($codes);

        $codes_return = [];
        $prices = $lbl->getReturnPrice(false);
        if ($prices !== false && is_array($prices) && count($prices) > 0) {
            foreach ($prices as $k => $price) {
                $codes_return['global'][] = $price->getProductGlobalCode();
                $codes_return['local'][] = $price->getProductLocalCode();
                $codes_return['price'][$price->getProductGlobalCode()] = $price->getTotalAmount();
                $codes_return['remote'][$price->getProductGlobalCode()] = $price->getQtdShpExChrg();
            }
        }

        $prices = $lbl->getReturnPrice(true);
        if ($prices !== false && is_array($prices) && count($prices) > 0) {
            foreach ($prices as $k => $price) {
                $codes_return['global'][] = $price->getProductGlobalCode();
                $codes_return['local'][] = $price->getProductLocalCode();
                $codes_return['price'][$price->getProductGlobalCode()] = $price->getTotalAmount();
                $codes_return['remote'][$price->getProductGlobalCode()] = $price->getQtdShpExChrg();
            }
        }

        $this->defConfParams['return_methods'] = json_encode($codes_return);

        return true;
    }

    public function getProductSizes($item, $itemData, $product, &$packer, $attributeWidth, $attributeHeight, $attributeLength, $currencyKoef)
    {
        $children = $item->getChildrenItems();

        $isSize = 0;
        $productType = $this->productRepository->getById($itemData['product_id'])->getTypeId();
        $parentWeight = 0;

        $parentWidth = 0;
        $parentLength = 0;
        $parentHeight = 0;

        if ($productType != "virtual") {
            if (
                !empty($product[$attributeWidth])
                && !empty($product[$attributeHeight])
                && !empty($product[$attributeLength])
            ) {
                $parentWidth = $product[$attributeWidth];
                $parentLength = $product[$attributeLength];
                $parentHeight = $product[$attributeHeight];
                if (!$item->getHasChildren() || $productType != 'configurable') {
                    $packer->addItem(
                        new PackerItem(
                            $item->getBasePrice() * $currencyKoef,
                            $parentWidth * 1000,
                            $parentLength * 1000,
                            $parentHeight * 1000,
                            (empty($product['weight']) ? 0.1 : $product['weight']) * 1000,
                            true
                        )
                    );
                    $isSize++;
                }
            }

            $parentWeight = $product['weight']??0;
        }

        if ($children && count($children) > 0) {
            foreach ($children as $child) {
                $productChildOrigin = $this->productRepository->getById($child->getProduct()->getId());
                $productChild = $productChildOrigin->getData();
                $productType = $productChildOrigin->getTypeId();

                if ($productType != "virtual") {
                    if (
                    (!empty($productChild[$attributeWidth])
                        && !empty($productChild[$attributeHeight])
                        && !empty($productChild[$attributeLength]))
                    || (!empty($parentWidth) && !empty($parentLength) && !empty($parentHeight))
                    ) {
                        $packer->addItem(
                            new PackerItem(
                                0,
                                (!empty($productChild[$attributeWidth])?$productChild[$attributeWidth]:$parentWidth) * 1000,
                                (!empty($productChild[$attributeLength])?$productChild[$attributeLength]:$parentLength) * 1000,
                                (!empty($productChild[$attributeHeight])?$productChild[$attributeHeight]:$parentHeight) * 1000,
                                (!empty($productChild['weight'])?$productChild['weight']:$parentWeight) * 1000,
                                true
                            )
                        );
                        $isSize++;
                    }
                }
            }
        }

        if ($isSize > 0) {
            return $product;
        }

        return false;
    }

    public function setPackageDefParams($itemData = null)
    {
        $defParArr_1['weight'] = $itemData !== null && isset($itemData['weight']) ? $itemData['weight'] : $this->totalWeight;
        $defParArr_1['currencycode'] = $this->_conf->getStoreConfig('dhllabel/ratepayment/currencycode', $this->storeId);
        $defParArr_1['width'] = $itemData !== null && isset($itemData['width']) ? $itemData['width'] : '';
        $defParArr_1['height'] = $itemData !== null && isset($itemData['height']) ? $itemData['height'] : '';
        $defParArr_1['length'] = $itemData !== null && isset($itemData['length']) ? $itemData['length'] : '';
        $defParArr_1['depth'] = $itemData !== null && isset($itemData['length']) ? $itemData['length'] : '';
        if (empty($itemData['empty_weight'])) {
            $defParArr_1['empty_weight'] = round((float)str_replace(',', '.', $this->_conf->getStoreConfig('dhllabel/weightdimension/packweight', $this->storeId)), 1) > 0 ? round((float)str_replace(',', '.', $this->_conf->getStoreConfig('dhllabel/weightdimension/packweight', $this->storeId)), 1) : '0';
        } else {
            $defParArr_1['empty_weight'] = $itemData['empty_weight'];
        }

        $defParArr_1['packweight'] = $defParArr_1['empty_weight'];

        $defParArr_1['box'] = $defParArr_1['width'] . 'x' . $defParArr_1['height'] . 'x' . $defParArr_1['depth'] . 'x' . $defParArr_1['empty_weight'];
        return $defParArr_1;
    }

    public function getShipRate($lbl)
    {
        return $lbl->getShipRate();
    }

    public function getLabel($order, $type, $shipment_id = null, $params)
    {
        if ($this->order === null) {
            $this->order = $order;
        }

        unset($order);
        $this->type = $type;
        unset($type);
        $this->shipment_id = $shipment_id;
        unset($shipment_id);
        if ($this->shipment_id !== null) {
            $this->shipment = $this->shipmentRepository->get($this->shipment_id);
        }

        $this->storeId = $this->order->getStoreId();

        $lbl = $this->dhl;
        $lbl = $this->setParams($lbl, $params, isset($params['package']) ? $params['package'] : []);
        $dhll2 = null;
        if ($this->type == 'shipment' || $this->type == 'invert') {
            $dhll = $lbl->getShip($this->type, $this->storeId);
            if (isset($params['default_return']) && $params['default_return'] == 1) {
                $lbl->serviceCode = array_key_exists('default_return_servicecode', $params) ? $params['default_return_servicecode'] : '';

                if (isset($params['return_methods'])) {
                    $shippingMethods = json_decode($params['return_methods'], true);
                    if (isset($shippingMethods['global'])) {
                        $lbl->serviceLocalCode = array_key_exists('return_methods', $params) ?
                            $shippingMethods['local'][array_search($lbl->serviceCode, $shippingMethods['global'])] : '';
                    }
                }

                $dhll2 = $lbl->getShipFrom($this->type, $this->storeId);
            }
        } elseif ($this->type == 'refund') {
            $dhll = $lbl->getShipFrom($this->type, $this->storeId);
        } elseif ($this->type == 'ajaxprice_shipment') {
            $dhll = $lbl->getShipPrice(true);
            $dhllFalse = $lbl->getShipPrice(false);
            if (!empty($dhll) && !empty($dhllFalse)) {
                $dhll = array_merge($dhll, $dhllFalse);
            }
            if(empty($dhll) && !empty($dhllFalse)){
                $dhll = $dhllFalse;
            }
            return $dhll;
        } elseif ($this->type == 'ajaxprice_invert') {
            $dhll = $lbl->getShipPrice(true);
            $dhllFalse = $lbl->getShipPrice(false);
            if (!empty($dhll) && !empty($dhllFalse)) {
                $dhll = array_merge($dhll, $dhllFalse);
            }
            return $dhll;
        } elseif ($this->type == 'ajaxprice_refund') {
            $ii2 = 0;
            foreach ($params['package'] as $package) {
                $lbl->packages[0] = $package;
                $dhll[$ii2] = $lbl->getReturnPrice(true, $this->storeId);
                $dhllFalse = $lbl->getReturnPrice(false, $this->storeId);
                if (!empty($dhllFalse)) {
                    $dhll[$ii2] = array_merge($dhll[$ii2], $dhllFalse);
                }

                $ii2++;
            }

            return $dhll;
        } else {
            return false;
        }

        return $this->saveDB($dhll, $dhll2, $params, $lbl);
    }

    public function setParams($lbl, $params, $packages)
    {
        if ($lbl === null) {
            $lbl = $this->dhl;
        }

        $configOptions = new Options();
        $lbl->handy = $this;
        $lbl->packages = $packages;

        $lbl->UserID = $this->_conf->getStoreConfig('dhllabel/credentials/userid', $this->storeId);
        $lbl->Password = $this->_conf->getStoreConfig('dhllabel/credentials/password', $this->storeId);
        $lbl->shipperNumber = $this->_conf->getStoreConfig('dhllabel/credentials/shippernumber', $this->storeId);

        $address = $this->addresses->getAddressesById((!empty($params['shipper_no']) ? $params['shipper_no'] : 0));

        if (empty($address)) {
            return $lbl;
        }

        $lbl->codOrderId = $this->order->getId();

        $lbl->shipmentDescription = isset($params['shipmentdescription']) ? $this->_conf->escapeXML($params['shipmentdescription']) : '';
        $lbl->shipperName = $this->_conf->escapeXML($address->getCompany());
        $lbl->shipperAttentionName = $this->_conf->escapeXML($address->getAttention());
        $lbl->shipperPhoneNumber = $this->_conf->escapeXML($address->getPhone());
        $lbl->shipperAddressLine1 = $this->_conf->escapeXML($address->getStreetOne());
        $lbl->shipperAddressLine2 = $this->_conf->escapeXML($address->getStreetTwo());
        $lbl->shipperCity = $this->_conf->escapeXML($address->getCity());
        $lbl->shipperStateProvinceName = $this->_conf->escapeXML($address->getProvinceCode());
        $lbl->shipperPostalCode = $this->_conf->escapeXML($address->getPostalCode());
        $lbl->shipperCountryCode = $this->_conf->escapeXML($address->getCountry());
        $lbl->shipperStateProvinceCode = $this->_conf->escapeXML($configOptions->getProvinceCode($lbl->shipperStateProvinceName));
        if (!empty($lbl->shipperCountryCode)) {
            $lbl->shipperCountryName = $this->country->loadByCode($lbl->shipperCountryCode)->getName();
        }

        if ($lbl->shipperCountryCode == 'US' && $lbl->shipperStateProvinceCode == 'PR') {
            $lbl->shipperCountryCode = 'PR';
            $lbl->shipperStateProvinceCode = '';
        }

        if ($lbl->shipperCountryCode == 'BL') {
            $lbl->shipperCountryCode = 'XY';
        }

        $lbl->shiptoCompanyName = $this->_conf->escapeXML($params['shiptocompanyname']);
        $lbl->shiptoAttentionName = $this->_conf->escapeXML($params['shiptoattentionname']);
        $lbl->shiptoPhoneNumber = $this->_conf->escapeXML($params['shiptophonenumber']);
        $lbl->shiptoAddressLine1 = trim($this->_conf->escapeXML($params['shiptoaddressline1']));
        $lbl->shiptoAddressLine2 = trim($this->_conf->escapeXML($params['shiptoaddressline2']));
        $lbl->shiptoAddressLine3 = trim($this->_conf->escapeXML($params['shiptoaddressline3']));
        $lbl->shiptoCity = $this->_conf->escapeXML($params['shiptocity']);
        $lbl->shiptoStateProvinceName = $this->_conf->escapeXML($params['shiptostateprovincecode']);
        $lbl->shiptoStateProvinceCode = $this->_conf->escapeXML($configOptions->getProvinceCode($params['shiptostateprovincecode'], $params['shiptocountrycode']));
        $lbl->shiptoPostalCode = $this->_conf->escapeXML($params['shiptopostalcode']);
        $lbl->shiptoCountryCode = $this->_conf->escapeXML($params['shiptocountrycode']);
        $lbl->shiptoCountryName = !empty($params['shiptocountrycode']) ? $this->country->loadByCode($params['shiptocountrycode'])->getName() : "";

        if ($lbl->shiptoCountryCode == 'US' && $lbl->shiptoStateProvinceCode == 'PR') {
            $lbl->shiptoCountryCode = 'PR';
            $lbl->shiptoStateProvinceCode = '';
        }

        if ($lbl->shiptoCountryCode == 'BL') {
            $lbl->shiptoCountryCode = 'XY';
        }


        $lbl->serviceCode = array_key_exists('serviceCode', $params) ? $params['serviceCode'] : '';
        if (isset($params['shipping_methods']) && !empty($lbl->serviceCode)) {
            $shippingMethods = json_decode($params['shipping_methods'], true);
            if (isset($shippingMethods['global'])) {
                $lbl->serviceLocalCode = array_key_exists('shipping_methods', $params) ?
                    $shippingMethods['local'][array_search($lbl->serviceCode, $shippingMethods['global'])] : '';
            }
        }

        $lbl->weightUnits = array_key_exists('weightunits', $params) ? $params['weightunits'] : '';

        $lbl->includeDimensions = array_key_exists('includedimensions', $params) ? $params['includedimensions'] : 0;
        $lbl->unitOfMeasurement = array_key_exists('unitofmeasurement', $params) ? $params['unitofmeasurement'] : '';

        $lbl->codYesNo = array_key_exists('cod', $params) ? $params['cod'] : '';
        $lbl->currencyCode = array_key_exists('currencycode', $params) ? $params['currencycode'] : '';
        $lbl->codMonetaryValue = array_key_exists('codmonetaryvalue', $params) ? $params['codmonetaryvalue'] : '';
        $lbl->insuredMonetaryValue = array_key_exists('insured_monetary_value', $params) ? $params['insured_monetary_value'] : 0;

        if ($params['shipper_vat_number'] != ''/* && $lbl->shiptoCountryCode == 'NO'*/) {
            $lbl->shipperEIN = $params['shipper_vat_number'];
        }

        if ($lbl->shiptoCountryCode != $lbl->shipperCountryCode) {
            $lbl->consigneeEIN = $this->_conf->getStoreConfig('dhllabel/ratepayment/consignee_vat_number', $this->storeId);
        }

        if (array_key_exists('qvn', $params) && $params['qvn'] > 0) {
            $lbl->qvn = 1;
            $lbl->qvn_email_message = isset($params['qvn_message']) ?
                $this->_conf->escapeXML($params['qvn_message']) : "";
        }

        $lbl->qvn_email_shipper = $params['qvn_email_shipper'];
        $lbl->qvn_email_shipto = $params['qvn_email_shipto'];
        $lbl->shiptoVatid = $params['shiptovat'];

        if ($params['dhlaccount'] != "S" && $params['dhlaccount'] != "R") {
            $lbl->dhlAccount = 1;
            $lbl->accountData = $this->account->create()->load($params['dhlaccount']);
        } else {
            $lbl->dhlAccount = 0;
            $lbl->accountData = $params['dhlaccount'];
        }

        if ($params['dhlaccount_duty'] != "S" && $params['dhlaccount_duty'] != "R") {
            $lbl->dhlAccountDuty = 1;
            $lbl->accountDataDuty = $this->account->create()->load($params['dhlaccount_duty']);
        } else {
            $lbl->dhlAccountDuty = 0;
            $lbl->accountDataDuty = $params['dhlaccount_duty'];
        }

        $lbl->testing = $params['testing'];
        $lbl->packageType = isset($params['packagingtypecode']) ?
            $this->_conf->escapeXML($params['packagingtypecode']) : "";
        $lbl->ReferenceId = array_key_exists('reference_id', $params) ? $params['reference_id'] : '';
        $declared_value = explode('.', (string)round($params['declared_value'], 2));
        if (count($declared_value) > 1 && strlen($declared_value[1]) == 1) {
            $declared_value = round($params['declared_value'], 2) . '0';
        } else {
            $declared_value = round($params['declared_value'], 2);
        }

        $lbl->declaredValue = $declared_value;

        $lbl->print_type = $this->_conf->getStoreConfig('dhllabel/printing/printer', $this->storeId);
        if ($lbl->print_type == "PDF") {
            $lbl->print_type_format = $this->_conf
                ->getStoreConfig('dhllabel/printing/printer_format_pdf', $this->storeId);
        } else {
            $lbl->print_type_format = $this->_conf
                ->getStoreConfig('dhllabel/printing/printer_format_thermal', $this->storeId);
        }

        $lbl->print_type_refund = $this->_conf->getStoreConfig('dhllabel/printing/printer_refund', $this->storeId);
        if ($lbl->print_type_refund == "PDF") {
            $lbl->print_type_format_refund = $this->_conf
                ->getStoreConfig('dhllabel/printing/printer_format_pdf_refund', $this->storeId);
        } else {
            $lbl->print_type_format_refund = $this->_conf
                ->getStoreConfig('dhllabel/printing/printer_format_thermal_refund', $this->storeId);
        }

        $lbl->print_type_dpi = $this->_conf->getStoreConfig('dhllabel/printing/dpi', $this->storeId);
        if (empty($lbl->print_type_dpi) || $lbl->print_type_dpi < 200 || $lbl->print_type_dpi > 300) {
            $lbl->print_type_dpi = 300;
        }

        $lbl->requestArchiveDoc = "N";
        if ($this->_conf->getStoreConfig('dhllabel/printing/archive', $this->storeId) == 1) {
            $lbl->requestArchiveDoc = "Y";
        }

        $lbl->invoiceProducts = $params['invoice_product'];
        $lbl->termsOfTrade = $params['terms_of_trade'];
        $lbl->placeOfIncoterm = $params['place_of_incoterm'];
        $lbl->invoiceType = $params['invoice_type'];
        $lbl->eoriNumber = $params['eori_number'];
        return $lbl;
    }

    public function saveDB($dhll, $dhll2 = null, $params, $lbl = null)
    {
        if ($this->order->getId() > 0) {
            $colls2 = $this->itemsFactory->create()->getCollection()
                ->addFieldToFilter('order_id', $this->order->getId())->addFieldToFilter('type', $this->type)
                ->addFieldToFilter('lstatus', 1);
            if (count($colls2) > 0) {
                foreach ($colls2 as $c) {
                    $c->delete();
                }
            }

            $request_data = $dhll['request'];
            $request_dataArr = (array)simplexml_load_string(utf8_encode($request_data));
            //echo var_export($request_dataArr['DHLInvoiceType']); exit;
            $response_data = (array)simplexml_load_string(utf8_encode($dhll['response']));
            $note = isset($response_data['Note']) ? (array)$response_data['Note'] : [];
            if (array_key_exists('ActionNote', $note) && $note['ActionNote'] === 'Success') {
                if ($this->_conf->getStoreConfig('dhllabel/additional_settings/orderstatuses', $this->storeId) != '') {
                    $this->order->setStatus($this->_conf
                        ->getStoreConfig('dhllabel/additional_settings/orderstatuses', $this->storeId), true);
                    $this->order->save();
                }

                $trackingnumber = $response_data['AirwayBillNumber'];
                $LabelImage = (array)$response_data['LabelImage'];
                $outputType = strtolower($LabelImage['OutputFormat']);
                $outputPDF = base64_decode($LabelImage['OutputImage']);
                $copyLabels = $this->_conf->getStoreConfig('dhllabel/additional_settings/multiple_pdf_label',
                    $this->storeId);
                $priceForShipment = !empty($response_data['ShippingCharge']) ? $response_data['ShippingCharge'] : 0;
                if ($copyLabels > 1) {
                    $pdf = new \Zend_Pdf();
                    $pdf2 = \Zend_Pdf::parse($outputPDF);
                    for ($i_pdf = 0; $i_pdf < $copyLabels; $i_pdf++) {
                        foreach ($pdf2->pages as $k => $page) {
                            $template2 = clone $pdf2->pages[$k];
                            $page2 = new \Zend_Pdf_Page($template2);
                            $pdf->pages[] = $page2;
                        }
                    }

                    $outputPDF = $pdf->render();
                }

                $path = $this->_conf->getBaseDir('media') . '/dhllabel/label/';
                if (file_put_contents($path . 'label' . $trackingnumber . '.' . $outputType, $outputPDF)) {
                    $dhllabel = $this->itemsFactory->create();
                    $dhllabel->setTitle('Order ' . $this->order->getIncrementId() . ' TN' . $trackingnumber);
                    $dhllabel->setOrderId($this->order->getId());
                    $dhllabel->setOrderIncrementId($this->order->getIncrementId());
                    $dhllabel->setCustomerName($this->order->getCustomerName());
                    $dhllabel->setType($this->type);
                    $dhllabel->setType2($this->type);
                    $dhllabel->setTrackingnumber($trackingnumber);
                    $dhllabel->setLabelname('label' . $trackingnumber . '.' . $outputType);
                    $dhllabel->setTypePrint($outputType);
                    $dhllabel->setStatustext(__('Successfully'));
                    $dhllabel->setLstatus(0);
                    $dhllabel->setInvoiceType($request_dataArr['DHLInvoiceType']??'CMI');
                    $shipping_methods = json_decode($params['shipping_methods'], true);
                    $dhllabel->setPrice((!empty($priceForShipment) ? $priceForShipment : (isset($shipping_methods['price'][$params['serviceCode']]) ?
                        round($shipping_methods['price'][$params['serviceCode']], 2) : 0)));
                    $dhllabel->setCurrency($params['currencycode']);
                    $dhllabel->setStoreId($this->order->getStoreId());
                    $dhllabel->setCreatedTime(Date("Y-m-d H:i:s"));
                    $dhllabel->setUpdateTime(Date("Y-m-d H:i:s"));
                    if ($dhllabel->save()) {
                        if ($this->_conf->getStoreConfig('dhllabel/printing/automatic_printing', $this->storeId) == 1) {
                            $this->_conf->sendPrint($outputPDF, $this->storeId);
                        }

                        /* Create Invoice */
                        if ($this->_conf->getStoreConfig('dhllabel/paperless/create_pdf', $this->storeId) == 1 && !empty($LabelImage['MultiLabels'])) {
                            $multiLabels = (array)$LabelImage['MultiLabels'];
                            foreach ($multiLabels as $multiLabel) {
                                $exLabel = (array)$multiLabel;
                                if (!empty($exLabel['DocName']) && $exLabel['DocName'] == "CustomInvoiceImage" && !empty($exLabel['DocImageVal'])) {
                                    file_put_contents($path . 'invoice_' . $trackingnumber . '.' . strtolower($exLabel['DocFormat']), base64_decode($exLabel['DocImageVal']));
                                }
                            }
                        }
                        /* END Create Invoice */

                    }

                    if ($this->shipment_id === null && $this->type != "refund") {
                        if ($this->order->canShip() && count($this->order->getShipmentsCollection()) == 0) {
                            if ($this->_registry->registry('current_shipment')) {
                                $this->_registry->unregister('current_shipment');
                            }

                            $items = [];
                            foreach ($this->order->getAllItems() as $item) {
                                $items[$item->getId()] = $item->getQtyToShip();
                            }

                            $shipmentLoader = $this->shipmentLoaderFactory->create();
                            $shipmentLoader->setOrderId($this->order->getId());
                            $shipmentLoader->setShipment($items);
                            $shipment = $shipmentLoader->load();
                            if ($shipment) {
                                $shipment->register();
                                $shipment->getOrder()->setIsInProcess(true);
                                $this->shipmentRepository->save($shipment);
                                $this->orderRepository->save($shipment->getOrder());
                                /*$transactionSave = $this->_objectManager->create(
                                    'Magento\Framework\DB\Transaction'
                                );
                                $transactionSave->addObject(
                                    $shipment
                                )->addObject(
                                    $shipment->getOrder()
                                );
                                $transactionSave->save();*/
                            }

                            $this->shipment = $this->shipmentRepository->get($shipment->getId());
                            $this->shipment_id = $this->shipment->getId();
                        } else {
                            $this->shipment = $this->order->getShipmentsCollection()->getFirstItem();
                            $this->shipment_id = $this->shipment->getId();
                        }
                    }

                    $dhllabel->setShipmentId($this->shipment_id);
                    if ($this->type != "refund") {
                        $dhllabel->setShipmentIncrementId($this->shipment->getIncrementId());
                    }

                    $dhllabel->save();
                    $this->label[] = $dhllabel;

                    if (isset($params['addtrack']) && $params['addtrack'] == 1 && $this->type == 'shipment') {
                        $trTitle = 'DHL';
                        if ($this->shipment) {
                            $track = $this->trackFactory->create()
                                ->setNumber(trim($trackingnumber))
                                ->setCarrierCode('dhl')
                                ->setTitle($trTitle);
                            $this->shipment->addTrack($track);
                            $this->shipmentRepository->save($this->shipment);
                            if ($this->_conf->getStoreConfig('dhllabel/shipping/track_send',
                                    $this->storeId) == 1) {
                                $this->shipmentServiceFactory->create()->notify($this->shipment->getId());
                            }
                        }
                    }

                    if (isset($params['default_return']) && $params['default_return'] == 1 && $this->type != "refund") {
                        $request_data = $dhll2['request'];
                        $response_data = (array)simplexml_load_string($dhll2['response']);
                        if (isset($response_data['Note'])) {
                            $note = isset($response_data['Note']) ? (array)$response_data['Note'] : [];
                            if (isset($response_data['AirwayBillNumber'])) {
                                $trackingnumber = $response_data['AirwayBillNumber'];
                                if (array_key_exists('ActionNote', $note) && $note['ActionNote'] === 'Success') {
                                    $LabelImage = (array)$response_data['LabelImage'];
                                    $outputPDF = base64_decode($LabelImage['OutputImage']);
                                    $priceForShipment = !empty($response_data['ShippingCharge']) ? $response_data['ShippingCharge'] : 0;
                                    $copyLabels = $this->_conf
                                        ->getStoreConfig('dhllabel/additional_settings/multiple_pdf_label', $this->storeId);
                                    if ($copyLabels > 1) {
                                        $pdf = new \Zend_Pdf();
                                        $pdf2 = \Zend_Pdf::parse($outputPDF);
                                        for ($i_pdf = 0; $i_pdf < $copyLabels; $i_pdf++) {
                                            foreach ($pdf2->pages as $k => $page) {
                                                $template2 = clone $pdf2->pages[$k];
                                                $page2 = new \Zend_Pdf_Page($template2);
                                                $pdf->pages[] = $page2;
                                            }
                                        }

                                        $outputPDF = $pdf->render();
                                    }

                                    if (file_put_contents($path . 'label' . $trackingnumber . '.' . $outputType, $outputPDF)) {
                                        $dhllabel = $this->itemsFactory->create();
                                        $dhllabel->setTitle('Order ' . $this->order->getIncrementId() . ' TN'
                                            . $trackingnumber);
                                        $dhllabel->setOrderId($this->order->getId());
                                        $dhllabel->setOrderIncrementId($this->order->getIncrementId());
                                        $dhllabel->setCustomerName($this->order->getCustomerName());
                                        $dhllabel->setShipmentId($this->shipment_id);
                                        if ($this->type == "shipment") {
                                            $dhllabel->setShipmentIncrementId($this->shipment->getIncrementId());
                                        }

                                        $dhllabel->setType($this->type);
                                        $dhllabel->setType2('refund');
                                        $dhllabel->setTrackingnumber($trackingnumber);
                                        $dhllabel->setLabelname('label' . $trackingnumber . '.' . $outputType);
                                        $dhllabel->setStatustext(__('Successfully'));
                                        $dhllabel->setTypePrint($outputType);
                                        $dhllabel->setLstatus(0);
                                        $shipping_methods = json_decode($params['return_methods'], true);
                                        $dhllabel->setPrice((!empty($priceForShipment) ? $priceForShipment : (isset($shipping_methods['price'][$params['default_return_servicecode']]) ? round($shipping_methods['price'][$params['default_return_servicecode']], 2) : 0)));
                                        $dhllabel->setCurrency($params['currencycode']);
                                        $dhllabel->setStoreId($this->order->getStoreId());
                                        $dhllabel->setCreatedTime(Date("Y-m-d H:i:s"));
                                        $dhllabel->setUpdateTime(Date("Y-m-d H:i:s"));
                                        if ($dhllabel->save() !== false) {
                                            if ($this->_conf->getStoreConfig('dhllabel/printing/automatic_printing', $this->storeId) == 1) {
                                                $this->_conf->sendPrint($outputPDF, $this->storeId);
                                            }
                                        }

                                        $this->label2[] = $dhllabel;
                                    }

                                    if (isset($params['addtrack']) && $params['addtrack'] == 1
                                        && $this->type == 'shipment') {
                                        $trTitle = 'DHL (return)';
                                        if ($this->shipment) {
                                            $track = $this->trackFactory->create()
                                                ->setNumber(trim($trackingnumber))
                                                ->setCarrierCode('dhl')
                                                ->setTitle($trTitle);
                                            $this->shipment->addTrack($track);
                                        }
                                    }
                                }
                            }
                        } else {
                            $error = (array)$response_data['Response'];
                            $error = (array)$error['Status'];
                            $error = $error['Condition'];
                            $errordescArr = '';
                            $error = (array)$error;
                            if (!isset($error['ConditionData'])) {
                                foreach ($error as $err) {
                                    $errordesc = (array)$err;
                                    if (isset($errordesc['ConditionData'])) {
                                        $errordescArr .= $errordesc['ConditionData'] . '; ';
                                    }
                                }
                            } else {
                                $errordescArr .= $error['ConditionData'] . '; ';
                            }

                            $dhllabel = $this->itemsFactory->create();
                            $dhllabel->setTitle('Order ' . $this->order->getIncrementId());
                            $dhllabel->setOrderId($this->order->getId());
                            $dhllabel->setOrderIncrementId($this->order->getIncrementId());
                            $dhllabel->setCustomerName($this->order->getCustomerName());
                            $dhllabel->setShipmentId($this->shipment_id);
                            $dhllabel->setType($this->type);
                            $dhllabel->setType2('refund');
                            $dhllabel->setStatustext($errordescArr);
                            $dhllabel->setXmllog($request_data . $dhll2['response']);
                            $dhllabel->setLstatus(1);
                            $dhllabel->setStoreId($this->order->getStoreId());
                            $dhllabel->setCreatedTime(Date("Y-m-d H:i:s"));
                            $dhllabel->setUpdateTime(Date("Y-m-d H:i:s"));
                            $dhllabel->save();
                            $this->label2[] = $dhllabel;
                        }
                    }
                }

                return true;
            } else {
                $error = (array)$response_data['Response'];
                $error = (array)$error['Status'];
                $error = $error['Condition'];
                $errordescArr = '';
                $error = (array)$error;
                if (!isset($error['ConditionData'])) {
                    foreach ($error as $err) {
                        $errordesc = (array)$err;
                        if (isset($errordesc['ConditionData'])) {
                            $errordescArr .= $errordesc['ConditionData'] . '; ';
                        }
                    }
                } else {
                    $errordescArr .= $error['ConditionData'] . '; ';
                }

                $dhllabel = $this->itemsFactory->create();
                $dhllabel->setTitle('Order ' . $this->order->getIncrementId());
                $dhllabel->setOrderId($this->order->getId());
                $dhllabel->setOrderIncrementId($this->order->getIncrementId());
                $dhllabel->setCustomerName($this->order->getCustomerName());
                $dhllabel->setShipmentId($this->shipment_id);
                $dhllabel->setType($this->type);
                $dhllabel->setType2($this->type);
                $dhllabel->setStatustext($errordescArr);
                $dhllabel->setXmllog($request_data . $dhll['response']);
                $dhllabel->setLstatus(1);
                $dhllabel->setStoreId($this->order->getStoreId());
                $dhllabel->setCreatedTime(Date("Y-m-d H:i:s"));
                $dhllabel->setUpdateTime(Date("Y-m-d H:i:s"));
                $dhllabel->save();
                $this->label[] = $dhllabel;
            }

            return true;
        }

        return false;
    }

    public function deleteLabel($shipidnumber = null, $type = 'shipidnumber')
    {
        if ($shipidnumber !== null) {
            if ($type == 'label_ids') {
                $labels = $this->itemsFactory->create()->getCollection()->addFieldToFilter('dhllabel_id', ['in' => $shipidnumber]);
            } elseif ($type == 'shipidnumber') {
                $labels = $this->itemsFactory->create()->getCollection()->addFieldToFilter('shipment_id', ['in' => $shipidnumber]);
            }

            if (count($labels) > 0) {
                $this->order = null;
                $this->shipment_id = null;
                foreach ($labels as $model) {
                    if ($this->order === null) {
                        $this->order = $this->orderRepository->get($model->getOrderId());
                    }

                    if ($model->getShipmentId() > 0 && $this->shipment_id === null) {
                        $this->shipment_id = $model->getShipmentId();
                    }

                    if ($model->getLstatus() == 0) {
                        if (is_file($this->_conf->getBaseDir('media') . '/dhllabel/label/' . $model->getLabelname()) && file_exists($this->_conf->getBaseDir('media') . '/dhllabel/label/' . $model->getLabelname())) {
                            unlink($this->_conf->getBaseDir('media') . '/dhllabel/label/' . $model->getLabelname());
                        }
                        if ($model->getShipmentId() > 0) {
                            $shipm = $this->shipmentRepository->get($model->getShipmentId());
                            $tracks = $shipm->getAllTracks();
                            foreach ($tracks as $track) {
                                if ($track->getNumber() == $model->getTrackingnumber()) {
                                    $track->delete();
                                }
                            }
                        }
                    }

                    $model->delete();
                }

                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    protected function setPdfA4Height($height)
    {
        $this->pdfA4Height = $height;
        $this->pdfCurrentHeight = $this->pdfA4Height;
    }

    protected function pdfCalcTop($lineHeight)
    {
        $this->pdfCurrentHeight -= $lineHeight;
        if ($this->pdfCurrentHeight - 30 < 0) {
            $this->pdfCurrentHeight = $this->pdfA4Height - 24;
            $this->pdf->pages[] = $this->getPdfCurrentPage();
        }
    }

    protected function getPdfCurrentPage()
    {
        $this->pdfCurrentPage = new \Zend_Pdf_Page(\Zend_Pdf_Page::SIZE_A4);
        $font = \Zend_Pdf_Font::fontWithPath($this->configReader
                ->getModuleDir(Dir::MODULE_ETC_DIR, 'Infomodus_Dhllabel') . '/HelveticaWorld-Regular.ttf');
        $this->pdfCurrentPage->setFont($font, 10);
        return $this->pdfCurrentPage;
    }

    protected
    function getAttributeContent($productOriginObj, $code)
    {
        $value = '';
        if ($productOriginObj) {
            $value = $productOriginObj->getAttributeText($code);
            if (empty($value)) {
                $value = $productOriginObj->getData($code);
            }
        }

        return $value;
    }

    protected function _getBaseCurrencyKoef($from, $to)
    {
        return $this->_currencyFactory->create()->load(
            $from
        )->getAnyRate(
            $to
        );
    }
}
