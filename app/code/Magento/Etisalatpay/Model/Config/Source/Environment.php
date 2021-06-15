<?php

namespace Magento\Etisalatpay\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Environment implements ArrayInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 'test', 'label' => __('Test (Sandbox)')],
			['value' => 'production', 'label' => __('Production')]
		];
	}
}