<?php
namespace RLTSquare\ConfirmOrder\Model;
class Post extends \Magento\Framework\Model\AbstractModel
{
	protected function _construct()
	{
		$this->_init('RLTSquare\ConfirmOrder\Model\ResourceModel\Post');
	}
}