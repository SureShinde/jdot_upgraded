<?php

namespace Pentalogy\Callcourier\Model\Config\Source;
use Pentalogy\Callcourier\Model\Api;
class Cities implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return (new Api\Integration())->citiesToArray();
    }
}