<?php

namespace RLTSquare\OrderTrackingStatus\Block\OrderStatus;

use RLTSquare\CustomPrint\Model\Carrier;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Status
 * @package RLTSquare\OrderTrackingStatus\Block\OrderStatus
 */
class Status extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var Carrier
     */
    protected $carrier;

    /**
     * @var Call Courier Tracking API
     */
    protected $cc_tracking_api;
    /**
     * @var Json
     */
    protected $json;

    /**
     * Status constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Carrier $carrier
     * @param Json $json
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Pentalogy\Callcourier\Model\Api\Integration $ccTrackingApi,
        Carrier $carrier,
        Json $json,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        $this->cc_tracking_api = $ccTrackingApi;
        $this->request = $request;
        $this->order = $order;
        $this->messageManager = $messageManager;
        $this->carrier = $carrier;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\View\Element\Template
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function orderTracking()
    {
        $incrementId = $this->getRequest()->getParam('orderId'); // give order id
        $order = $this->order->loadByIncrementId($incrementId);
        $tracksCollection = $order->getTracksCollection();
        $responeData = null;
        $responeData['isOrderIdValid'] = ($order->getId() != null) ? true : false;
        $responeData['orderIncrementId'] = $incrementId;
        $responeData['shipped'] = array('status' => "Not Shipped", 'date' => null, 'courier' => null, 'trackNum' => null);
        $responeData['delivered'] = array('status' => "Not Delivered", 'date' => null);


        $responeData['tracking_info'][] = array(
            'status' => 'Order Placed',
            'date' => strtotime($order->getCreatedAt()),
            'location' => ''
        );

        //Adding Order Status History
        foreach (array_reverse($order->getStatusHistories()) as $status) {
            $statusData = $status->getData();
            if((!empty($statusData['is_customer_notified']) && $statusData['is_customer_notified'] != 0) ||
                (!empty($statusData['is_visible_on_front']) && $statusData['is_visible_on_front'] != 0)) {
                $status['comment'] = strip_tags($status['comment']);
                $name = '';
                if (strpos($status['comment'], '( by')) {
                    $status['comment'] = substr($status['comment'], 0, strpos($status['comment'], "( by"));
                    $name = ' By J. Team';
                }

                if (strpos($status['comment'], 'by')) {
                    $status['comment'] = substr($status['comment'], 0, strpos($status['comment'], "by"));
                    $name = ' (By J. Team)';
                }
                if (strpos($status['comment'], '___')) {
                    $status['comment'] = substr($status['comment'], 0, strpos($status['comment'], "___"));
                }

                if (!empty($name)) {
                    $status['comment'] = $status['comment'] . $name;
                }
                $responeData['tracking_info'][] = array(
                    'status' => empty($status['comment']) ? $status['status'] : $status['comment'],
                    'date' => strtotime($status['created_at']),
                    'location' => ''
                );
            }
        }

        foreach ($tracksCollection->getItems() as $track) {
            $trackingNumber = $track->getTrackNumber();
            $orderStatus = $order->getStatusLabel();
            $shippingMethod = $order->getShippingMethod();
            $responeData['shipping_method'] = $shippingMethod;

            $responeData['shipped']['status'] = "Shipped";
            $responeData['shipped']['date'] = strtotime($track->getCreatedAt());

            if ($track->getTitle() == 'Call Courier' && $orderStatus == 'Complete') {
                $responeData['shipped']['courier'] = "Call Courier";
                $responeData['shipped']['trackNum'] = $trackingNumber;
                $responeData['shipping_method'] = $track->getTitle();
                $trackingData = $this->cc_tracking_api->tracking($trackingNumber);
                if (!array_key_exists('Message', $trackingData) && !empty($trackingData) && count($trackingData) > 0) {
                    foreach ($trackingData as $detail) {
                        $detail = get_object_vars($detail);
                        if (strpos(strtolower($detail['ProcessDescForPortal']), 'delivered') !== false &&
                            strpos(strtolower($detail['ProcessDescForPortal']), 'undelivered') === false) {
                            $responeData['delivered']['status'] = "Delivered";
                            $responeData['delivered']['date'] = strtotime($detail['TransactionDate']);
                        }

                        $responeData['tracking_info'][] = array(
                            'status' => $detail['ProcessDescForPortal'],
                            'date' => strtotime($detail['TransactionDate']),
                            'location' => $detail['HomeBranch']
                        );
                    }
                } else {
                    $responeData['error'] = "No tracking information is present against this order";
                }
            } else if ($shippingMethod == 'aalcs_lcs' && $orderStatus == 'Complete') {
                //LCS Tracking
                $responeData['shipped']['courier'] = "Leopards Courier";
                $responeData['shipped']['trackNum'] = $trackingNumber;
                $rawFormTrackingStatus = $this->lcsTracking($trackingNumber);
                if ($rawFormTrackingStatus !== false) {
                    $decodedTrackingStatus = $this->json->unserialize($rawFormTrackingStatus);
                    if (count($decodedTrackingStatus['packet_list'][0]['Tracking Detail']) > 0 && $decodedTrackingStatus['status'] == 1) {

                        foreach ($decodedTrackingStatus['packet_list'][0]['Tracking Detail'] as $detail) {

                            if (strpos(strtolower($detail['Status']), 'delivered') !== false) {
                                $responeData['delivered']['status'] = "Delivered";
                                $responeData['delivered']['date'] = strtotime($detail['Activity_datetime']);
                            }

                            $responeData['tracking_info'][] = array(
                                'status' => $detail['Status'],
                                'date' => strtotime($detail['Activity_datetime']),
                                'location' => ''
                            );
                        }
                    } else {
                        $responeData['error'] = "No tracking information is present against this order";
                    }
                }
            } else if ($shippingMethod == 'tcsshipping_tcsshipping' && $orderStatus == 'Complete') {
                //TCS Tracking
                $responeData['shipped']['courier'] = "TCS";
                $responeData['shipped']['trackNum'] = $trackingNumber;
                $rawFormTrackingStatus = $this->tcsTracking($trackingNumber);
                if ($rawFormTrackingStatus !== false) {
                    $decodedTrackingStatus = $this->json->unserialize($rawFormTrackingStatus);
                    if ($decodedTrackingStatus['returnStatus']['status'] == 'FAIL') {
                        $responeData['error'] = $decodedTrackingStatus['returnStatus']['message'];
                    } else if(array_key_exists("TrackDetailReply",$decodedTrackingStatus)){
                        $tcsTrackingInfo = $decodedTrackingStatus['TrackDetailReply'];
                        // Adding checkpoints
                        $itr = count($tcsTrackingInfo['Checkpoints']) - 1;
                        while ($itr >= 0) {
                            $responeData['tracking_info'][] = array(
                                'status' => $tcsTrackingInfo['Checkpoints'][$itr]['status'] . ' ' . $tcsTrackingInfo['Checkpoints'][$itr]['recievedBy'],
                                'date' => strtotime($tcsTrackingInfo['Checkpoints'][$itr]['dateTime']),
                                'location' => ''
                            );

                            --$itr;
                        }

                        if (array_key_exists('DeliveryInfo', $tcsTrackingInfo)) {
                            // Adding Delivery tracking
                            $itr = count($tcsTrackingInfo['DeliveryInfo']) - 1;
                            while ($itr >= 0) {
                                if (strpos(strtolower($tcsTrackingInfo['DeliveryInfo'][$itr]['status']), 'delivered') !== false) {
                                    $responeData['delivered']['status'] = "Delivered";
                                    $responeData['delivered']['date'] = strtotime($tcsTrackingInfo['DeliveryInfo'][$itr]['dateTime']);

                                }
                                $responeData['tracking_info'][] = array(
                                    'status' => $tcsTrackingInfo['DeliveryInfo'][$itr]['status'],
                                    'date' => strtotime($tcsTrackingInfo['DeliveryInfo'][$itr]['dateTime'])
                                );

                                --$itr;
                            }
                        }
                    }
                }
            } else if ($shippingMethod == 'dhl_P' && $orderStatus == 'Complete') {
                //DHL Tracking
                $responeData['shipped']['courier'] = "DHL";
                $responeData['shipped']['trackNum'] = $trackingNumber;
                $dhlTrackings = $this->dhlTracking($trackingNumber)->getAllTrackings();
                foreach ($dhlTrackings as $dhlTracking) {
                    $allDetails = $dhlTracking->getData();
                    if (array_key_exists('progressdetail', $allDetails)) {

                        foreach ($allDetails['progressdetail'] as $detail) {

                            if (strpos(strtolower($detail['activity']), 'delivered') !== false) {
                                $responeData['delivered']['status'] = "Delivered";
                                $responeData['delivered']['date'] = strtotime($detail['deliverydate'] . ' ' . $detail['deliverytime']);
                            }

                            $responeData['tracking_info'][] = array(
                                'status' => $detail['activity'],
                                'date' => strtotime($detail['deliverydate'] . ' ' . $detail['deliverytime']),
                                'location' => $detail['deliverylocation']
                            );
                        }

                    } else if (array_key_exists('error_message', $allDetails)) {
                        $responeData['error'] = $allDetails['error_message']->getText();
                    } else {
                        $responeData['error'] = "No tracking information is present against this order";
                    }
                }
            }
        }

        if (array_key_exists('tracking_info', $responeData)) {
            $responeData['tracking_info'] = array_reverse($responeData['tracking_info']);
        }

        return $responeData;
    }

    /**
     * @param int $trackingNumber
     * @return bool|string
     */
    public function lcsTracking($trackingNumber)
    {
        try {
            $ch = curl_init();
            $headers = array(
                'Accept: application/json',
                'Content-Type: application/json',
            );

            $apiKey = '14FAFCA0B304DBDF24C3ECD87D961D4E';
            $apiPassword = 'ABC@1234';

            curl_setopt($ch, CURLOPT_URL, 'http://new.leopardscod.com/webservice/trackBookedPacket/?api_key=' . $apiKey . '&api_password=' . $apiPassword . '&track_numbers=' . $trackingNumber);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Api Call Failed'));
        }
    }

    /**
     * @param int $trackingNumber
     * @return bool|string
     */
    public function tcsTracking($trackingNumber)
    {
        try {
            $ch = curl_init();
            $headers = array(
                'Accept: application/json',
                'Content-Type: application/json',
                'X-IBM-Client-Id: b9a89c82-a40a-428e-a81b-51358b81105b'
            );
            $consignmentNo = $trackingNumber;

            curl_setopt($ch, CURLOPT_URL, 'https://www.tcsexpress.com/Tracking/GetData?' . 'TrackingNumber=' . $consignmentNo);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Api Call Failed'));
        }
    }

    /**
     * @param int $trackingNumber
     * @return \Magento\Shipping\Model\Tracking\Result|null
     */
    public function dhlTracking($trackingNumber)
    {
        try {
            $dhlResponse = $this->carrier->getTracking($trackingNumber);
            return $dhlResponse;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('Api Call Failed'));
        }
    }
}