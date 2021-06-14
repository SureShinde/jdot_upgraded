<?php


namespace RLTSquare\OrderState\Block\Adminhtml\Order\View;

/**
 * Class History
 * @package RLTSquare\OrderState\Block\Adminhtml\Order\View
 */

class History extends \Magento\Sales\Block\Adminhtml\Order\View\History
{
    protected $authSession;

    /**
     * History constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */

    public function __construct(\Magento\Backend\Block\Template\Context $context,  \Magento\Backend\Model\Auth\Session $authSession,\Magento\Sales\Helper\Data $salesData, \Magento\Framework\Registry $registry, \Magento\Sales\Helper\Admin $adminHelper, array $data = [])
    {
        $this->authSession=$authSession;
        parent::__construct($context, $salesData, $registry, $adminHelper, $data);
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        $applicableId = [
            '1',
            '4356'
        ];
        $userRoleId=$this->authSession->getUser()->getRole()->getId();
        $cod_new_Status = ['pending' => 'Unconfirmed', 'verified' => 'verified', 's_order_confirmed' => 'Confirmed', 'forwarded_order' => 'Forwarded', 'under_inprocess' => 'Order In Process', 'under_incomplete' => 'Incomplete', 'productnotavailable' => 'Product Not Available', 'Engraving' => 'Engraving', 'requesttocancel' => 'Request to cancel', 'requestforagent' => 'Request for Agent call'];
        $cod_processing_Status = ['processing' => 'Processing'];
        $bt_new_Status = ['pending' => 'Unconfirmed', 's_order_confirmed' => 'Confirmed', 'forwarded_order' => 'Forwarded', 'under_inprocess' => 'Order In Process', 'under_incomplete' => 'Incomplete', 'productnotavailable' => 'Product Not Available', 'Engraving' => 'Engraving'];
        if (in_array($userRoleId, $applicableId)) {
            $cd_processing_Status = ['s_order_confirmed' => 'Confirmed','fraud' => 'Suspicious', 'processing' => 'Processing', 's_order_confirmed' => 'Confirmed', 'forwarded_order' => 'Forwarded', 'under_inprocess' => 'Order In Process', 'under_incomplete' => 'Incomplete', 'productnotavailable' => 'Product Not Available', 'Engraving' => 'Engraving', 'capture' => 'Capture'];
        }else
        {
            $cd_processing_Status = ['s_order_confirmed' => 'Confirmed','fraud' => 'Suspicious', 'processing' => 'Processing', 's_order_confirmed' => 'Confirmed', 'forwarded_order' => 'Forwarded', 'under_inprocess' => 'Order In Process', 'under_incomplete' => 'Incomplete', 'productnotavailable' => 'Product Not Available', 'Engraving' => 'Engraving'];

        }
        $cd_new_Status = ['payment_pending' => 'Pending Payment'];
        $paymentMehtod = $this->getOrder()->getPayment()->getMethod();
        $state = $this->getOrder()->getState();
        if ($state == 'new' && $paymentMehtod == 'cashondelivery') {
            $statuses = $this->getOrder()->getConfig()->getStateStatuses($state);
            $intersect = array_intersect($cod_new_Status, $statuses);
            return $intersect;
        } elseif ($state == 'processing' && $paymentMehtod == 'cashondelivery') {
            $statuses = $this->getOrder()->getConfig()->getStateStatuses($state);
            $intersect = array_intersect($cod_processing_Status, $statuses);
            return $intersect;
        } elseif ($state == 'new' && $paymentMehtod == 'banktransfer') {
            $statuses = $this->getOrder()->getConfig()->getStateStatuses($state);
            $intersect = array_intersect($bt_new_Status, $statuses);
            return $intersect;
        } elseif ($state == 'processing' && $paymentMehtod == 'banktransfer') {
            $statuses = $this->getOrder()->getConfig()->getStateStatuses($state);
            $intersect = array_intersect($cod_processing_Status, $statuses);
            return $intersect;
        } elseif ($state == 'new' && $paymentMehtod == 'etisalatpay') {
            $statuses = $this->getOrder()->getConfig()->getStateStatuses($state);
            $intersect = array_intersect($cd_new_Status, $statuses);
            return $intersect;
        } elseif ($state == 'processing' && $paymentMehtod == 'etisalatpay') {
            $statuses = $this->getOrder()->getConfig()->getStateStatuses($state);
            $intersect = array_intersect($cd_processing_Status, $statuses);
            return $intersect;
        } else {
            $statuses = $this->getOrder()->getConfig()->getStateStatuses($state);
            return $statuses;
        }
    }
}
