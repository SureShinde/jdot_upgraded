<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml;

/**
 * Items controller
 */
abstract class Items extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $_handy;
    protected $orderRepository;
    protected $itemsFactory;
    protected $logger;

    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Infomodus\Dhllabel\Helper\Handy $handy
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Infomodus\Dhllabel\Model\ItemsFactory $itemsFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Infomodus\Dhllabel\Helper\Handy $handy,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Infomodus\Dhllabel\Model\ItemsFactory $itemsFactory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_handy = $handy;
        $this->orderRepository = $orderRepository;
        $this->itemsFactory = $itemsFactory;
        $this->logger = $logger;
    }

    /**
     * Initiate action
     *
     * @return this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Infomodus_Dhllabel::items')->_addBreadcrumb(__('Labels'), __('Labels'));
        return $this;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed($this->_getAclResource());
    }

    protected function _getAclResource()
    {
        $action = strtolower($this->getRequest()->getActionName());
        switch ($action) {
            case 'index':
                $aclResource = 'Infomodus_Dhllabel::items';
                break;
            case 'save':
                $aclResource = 'Infomodus_Dhllabel::create';
                break;
            case 'delete':
                $aclResource = 'Infomodus_Dhllabel::delete';
                break;
            default:
                $aclResource = 'Infomodus_Dhllabel::dhllabel_acl';
                break;
        }
        return $aclResource;
    }
}
