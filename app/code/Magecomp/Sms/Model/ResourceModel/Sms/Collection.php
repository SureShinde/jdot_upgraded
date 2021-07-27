<?php
namespace Magecomp\Sms\Model\ResourceModel\Sms;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
	public function _construct()
	{
		$this->_init("Magecomp\Sms\Model\Sms", "Magecomp\Sms\Model\ResourceModel\Sms");
	}
}