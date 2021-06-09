<?php
/**
 * Adding reason for cancelling the old order
 *
 * @author  Dawood Gondal <dawood.gondal@rltsquare>
 */

namespace RLTSquare\CancelOrder\Controller\Adminhtml\Order\Edit;

/**
 * Class SaveOverride
 * @package RLTSquare\CancelOrder\Controller\Adminhtml\Order\Edit
 */
class SaveOverride extends \RLTSquare\CancelOrder\Controller\Adminhtml\Order\Create\SaveOverride
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::actions_edit';
}
