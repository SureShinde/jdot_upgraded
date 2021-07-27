<?php
namespace Magecomp\Sms\Model;

use Magento\Framework\Model\AbstractModel;

class Sms extends AbstractModel
{
    protected function _construct()
    {
       $this->_init("Magecomp\Sms\Model\ResourceModel\Sms");
    }
}
