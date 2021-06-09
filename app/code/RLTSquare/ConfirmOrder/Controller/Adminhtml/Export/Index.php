<?php
namespace RLTSquare\ConfirmOrder\Controller\Adminhtml\Export;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $_pageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory)
    {
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_pageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Export Confirmed Orders')));
        return $resultPage;

    }
}