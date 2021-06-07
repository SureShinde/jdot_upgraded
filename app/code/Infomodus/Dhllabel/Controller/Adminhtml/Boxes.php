<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml;

/**
 * Items controller
 */
abstract class Boxes extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Infomodus\Dhllabel\Model\BoxesFactory
     */
    protected $boxes;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Infomodus\Dhllabel\Model\BoxesFactory $boxes
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Infomodus\Dhllabel\Model\BoxesFactory $boxes,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->boxes = $boxes;
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
        $this->_setActiveMenu('Infomodus_Dhllabel::boxes')->_addBreadcrumb(__('Boxes'), __('Boxes'));
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
                $aclResource = 'Infomodus_Dhllabel::boxes_items';
                break;
            case 'save':
                $aclResource = 'Infomodus_Dhllabel::boxes_create';
                break;
            case 'delete':
                $aclResource = 'Infomodus_Dhllabel::boxes_delete';
                break;
            default:
                $aclResource = 'Infomodus_Dhllabel::dhllabel_acl';
                break;
        }
        return $aclResource;
    }
}
