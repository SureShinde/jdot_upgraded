<?php

namespace Magedelight\Storepickup\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magedelight\Storepickup\Model\StoreholidayFactory;
use Magento\Framework\Registry;
use Magedelight\Storepickup\Helper\Data as Storehelper;

abstract class storeholiday extends Action
{

    /**
     * storelocator factory
     *
     * @var AuthorFactory
     */
    protected $storeholidayFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * date filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @param Registry $registry
     * @param AuthorFactory $storelocatorFactory
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        StoreholidayFactory $storeholidayFactory,
        Context $context,
        Storehelper $storeHelper
    ) {
        $this->coreRegistry = $registry;
        $this->storeholidayFactory = $storeholidayFactory;
        $this->resultRedirectFactory = $context->getRedirect();
        $this->storeHelper = $storeHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magedelight\Storepickup\Model\Storelocator
     */
    protected function initStoreholiday()
    {
        $storeholidayId = (int) $this->getRequest()->getParam('holiday_id');
        $parent_id = (int) $this->getRequest()->getParam('holiday_parent_id');
        $md_store_id = $this->getRequest()->getParam('store');
        
        /** @var \Magedelight\Storepickup\Model\Storelocator $storelocator */
        if ($parent_id) {
            $storeholiday = $this->storeholidayFactory->create()
                         ->getCollection()
                         ->addFieldToFilter('holiday_parent_id', $parent_id)
                         ->addFieldToFilter('store_id', $md_store_id)
                         ->getFirstItem();
            
            $sholidayid = $storeholiday->getHolidayId();
            if (isset($sholidayid)) {
                $storeholiday->load($sholidayid);
            } else {
                $storeholiday->load($parent_id);
            }
        } else {
            $storeholiday = $this->storeholidayFactory->create();
        }
        /** @var \Magedelight\Storepickup\Model\Storelocator $storelocator */
        
        $this->coreRegistry->register('magedelight_storelocator_storeholiday', $storeholiday);
        return $storeholiday;
    }
}
