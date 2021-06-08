<?php
namespace Pentalogy\Callcourier\Block\Adminhtml\Order\View;
use Pentalogy\Callcourier\Model\Api;
class Custom extends \Magento\Backend\Block\Template
{

    public $orderId;
    public $objectManager;
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->orderId = $this->getRequest()->getParam('order_id');
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }
    public function getShipmentInfo()
    {
        $orderObj = $this->objectManager->create('Magento\Sales\Model\Order')
            ->load($this->orderId);
        return $this->_getOrderData($orderObj);
    }
    public function getCallCourierCitiesList(){
        return (new Api\Integration())->citiesToArray();
    }
    public function getServiceTypeList(){
        $services = (new Api\Integration())->getServiceType();
        $data = [];
        foreach ($services as $value){
            $data[$value->ServiceTypeID] = $value->ServiceType1;
        }

        return $data;
    }
    public function getSingleBookingUrl(){
        return $this->getUrl('pentalogy_callcourier/shipment/singlebooking');
    }
    public function getFormKey(){
        return $this->objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey();
    }
    private function _getOrderData($_order)
    {
        try {
            $description = array();
            $data = array();
            $data['ConsigneeRefNo'] = $_order->getIncrementId();
            $shippingAddress = $_order->getShippingAddress();
            $data['ConsigneeName'] = ucwords($shippingAddress->getName());
            $data['city'] = strtoupper($shippingAddress->getCity());
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $country = $objectManager->create('\Magento\Directory\Model\Country')
                ->load($shippingAddress->getCountryId())->getName();

            $data['Address'] = implode('', $shippingAddress->getStreet()) . " " . $shippingAddress->getRegion() . " " . $country;

            $data['ConsigneeCellNo'] = $shippingAddress->getTelephone();
            foreach ($_order->getAllVisibleItems() as $_item) {
                $description[] = $_item->getName() . " " . $_item->getSku() . " (" . (int)$_item->getQtyOrdered() . ")";
            }
            $data['Description'] = implode(',', $description);
            $data['CodAmount'] = (int)$_order->getGrandTotal();
            $data['Pcs'] = (int)$_order->getTotalQtyOrdered();
            $data['Weight'] = floatval($_order->getWeight());
            $data['payment'] =$_order->getPayment()->getMethod();
        } catch (Exception $e) {

        }
        return $data;
    }

    public function getRemarks(){
        return (new Api\Integration())->getRemarks();
    }

    public function getTracking(){
        $_order = $this->objectManager->create('Magento\Sales\Model\Order')
            ->load($this->orderId);
        $trackNumber = null;
        $trackingDetail = null;
        foreach ($_order->getShipmentsCollection() as $_shipment) {
            foreach($_shipment->getAllTracks() as $tracknum)
            {
                if($tracknum->getData('carrier_code')==Api\Integration::COURIER_CODE){
                    $trackNumber=$tracknum->getNumber();
                }
            }
        }
        if($trackNumber){
            $trackingDetail['data'] = (new Api\Integration())->tracking($trackNumber);
            $trackingDetail['cn'] = $trackNumber;
        }
        return $trackingDetail;
    }

    public function getTrackingLink(){
        return Api\Integration::TRACKING_LINK;
    }
}
