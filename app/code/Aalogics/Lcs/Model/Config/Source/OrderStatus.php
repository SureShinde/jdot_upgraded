<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 4/13/2018
 * Time: 11:41 AM
 */

namespace Aalogics\Lcs\Model\Config\Source;

class OrderStatus implements \Magento\Framework\Option\ArrayInterface
{

    protected $_options = null;
    private $_obj;

    public function __construct()
    {
        //get Object of Order/Status/Collection thorough model class
        $manager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->obj = $manager->create('Magento\Sales\Model\ResourceModel\Order\Status\Collection');
    }

    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = [];

            foreach ($this->obj->toOptionArray() as $group) {
                //set Order/Status/Collection into <option> tag
                $this->_options[] = [
                    'value' => $group['label'],
                    'label' => $group['value'],
                ];
            }
            ksort($this->_options);
        }
        return $this->_options;
    }
}