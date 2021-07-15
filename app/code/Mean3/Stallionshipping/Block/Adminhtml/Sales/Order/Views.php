<?php
 
namespace  Mean3\Stallionshipping\Block\Adminhtml\Sales\Order;

use Magento\Sales\Block\Adminhtml\Order\View;

class Views extends View
{
    public function beforeSetLayout(View $subject)
    {
        $this->removeButton('edit');

        $addButtonProps = [
            'id' => 'Stallion',
            'label' => __('Stallion'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->getCustomActionListOptions(),
        ];
        $subject->addButton('test',$addButtonProps);
    }
    protected function getCustomActionListOptions()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
            $trackingNumber = '';

            $tracksCollection =  $order->getTracksCollection();
            foreach ($tracksCollection->getItems() as $track) {
                $trackingNumber = $track->getTrackNumber();
                $trackTitle = $track->getTitle();
            }

        $url = "https://stalliondeliveries.com/trackParcel?Id=".$trackingNumber;
        
        $splitButtonOptions=[
            'action_1'=>['label'=>__('Book Through Stallion'),'onclick'=>"confirmSetLocation('Are you sure you want to book this?', '{$this->getBookUrl()}')"],
        ];
        
        if(isset($trackTitle) && $trackTitle == "Stallion Shipping "){
            $splitButtonOptions=[    
                'action_2'=>['label'=>__('Print Stallion waybill'),'onclick'=>"confirmSetLocation('Are you sure you want to book this?', '{$this->getPrintUrl()}')"],
                'action_3'=>['label'=>__('Track Stallion status'),'onclick'=>'window.open(\'' . $url . '\',\'_blank\')'],
            ];

        }
        return $splitButtonOptions;
    }

    public function getBookUrl()
    {
        return $this->getUrl('stallionshipping/order/book', ['_current'=>true]);
    }

    public function getPrintUrl()
    {
        return $this->getUrl('stallionshipping/order/SinglePrint', ['_current'=>true]);
    }
}
?>
