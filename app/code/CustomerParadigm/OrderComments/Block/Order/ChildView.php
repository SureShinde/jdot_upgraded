<?php
/**
 * Created by PhpStorm.
 * User: amber
 * Date: 03/01/17
 * Time: 12:06 PM
 */
namespace CustomerParadigm\OrderComments\Block\Order;
use Magento\Sales\Block\Order\View as ParentView;

class ChildView extends ParentView //\Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Payment\Helper\Data $paymentHelper,
        array $data = []
    ){
        parent::__construct($context, $registry,$httpContext,$paymentHelper,$data);
    }

}