<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Aalogics\Lcs\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class CityOptions implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_resource = $resource;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
         foreach ($this->getCities() as $field) {
            $options[] = [
                'label' => $field['default_name'],
                'value' => strtolower($field['default_name'])
            ];
        }
        return $options;
    }

    public function getCities()
    {
        $adapter = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $select = $adapter->select()
                    ->from('pakistan_cities_lcs')
                    ->order('default_name asc');
        return $adapter->fetchAll($select);
    }
}