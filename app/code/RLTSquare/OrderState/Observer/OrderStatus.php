<?php

namespace RLTSquare\OrderState\Observer;
use \Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
/**
 * Class OrderStatus
 * @package RLTSquare\OrderState\Observer
 */
class OrderStatus implements \Magento\Framework\Event\ObserverInterface
{
    protected $product;
    protected $options;
    public function __construct(Product $product,Option $option)
    {
        $this->product = $product;
        $this->options = $option;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $items = $order->getItems();
        $paymentMethod=$order->getPayment()->getMethod();
        $applicableSku = [
            'PM101473-100-999-M',
            '15000008-100-NIL',
            'PL089628-100-999-L',
            'PM089626-100-999-M',
            '02034612-100-999',
            '02031081-100-999',
            '02033671-100-999',
            '02032644-100-999'
        ];
        $applicablePayment=[
            'cashondelivery',
            'banktransfer'
        ];
        foreach ($items as $item) {
            $productId= $item->getProductId();
            $productLoad = $this->product->load($productId);
            $customOptions = $this->options->getProductOptionCollection($productLoad);
            if($customOptions)
            {
                $data = $customOptions->getData();
                foreach ($data as $d)
                {
                    $title = $d['title'];
                    if ($title == 'Engrave Your Text' && in_array($paymentMethod,$applicablePayment)) {
                        $order->setOrderEngrave('ENGRAVING');
                        $order->save();
                    }
                }
            }


        }
    }
}