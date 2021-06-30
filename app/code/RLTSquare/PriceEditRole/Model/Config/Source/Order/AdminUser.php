<?php
namespace RLTSquare\PriceEditRole\Model\Config\Source\Order;

use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class AdminUser implements ArrayInterface
{
    protected $adminUser;

    public function __construct(
        CollectionFactory $collectionFactoryUser
    ) {
        $this->adminUser = $collectionFactoryUser;
    }

    public function toOptionArray()
    {
        $adminUsers = [];

        foreach ($this->adminUser->create() as $user) {
            $adminUsers[] = [
                'value' => $user->getId(),
                'label' => $user->getName()
            ];
        }

        return $adminUsers;
    }
}