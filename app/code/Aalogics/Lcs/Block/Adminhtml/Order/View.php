<?php

/**
 * Copyright © Aalogics Ltd.All rights reserved.
 *
 * @package    Aalogics_Dropship
 * @copyright  Copyright © Aalogics Ltd (http://www.aalogics.com)
  */

namespace Aalogics\Lcs\Block\Adminhtml\Order;

class View extends \Magento\Sales\Block\Adminhtml\Order\View
{

    protected $_cityOption;
    protected $_context;
    protected $_lcsHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        \Aalogics\Lcs\Model\Config\Source\CityOptions $cityOption,
        \Aalogics\Lcs\Helper\Data $lcsHelper,
        array $data = []
    ) {
        $this->_cityOption = $cityOption;
        $this->_context = $context;
        $this->_lcsHelper = $lcsHelper;
        parent::__construct($context, $registry, $salesConfig, $reorderHelper, $data);
    }

    public function _getCitiesOption(){
        $cities = $this->_cityOption->toOptionArray();
        return $cities;
    }

    /**
     * lcs Ship URL getter
     *
     * @return string
     */
    public function getLCSUrl()
    {
        return $this->getUrl('aalcs/*/ship');
    }

}
