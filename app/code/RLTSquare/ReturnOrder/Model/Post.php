<?php
namespace RLTSquare\ReturnOrder\Model;
class Post extends \Magento\Framework\Model\AbstractModel
{
	protected function _construct()
	{
		$this->_init('RLTSquare\ReturnOrder\Model\ResourceModel\Post');
	}
}