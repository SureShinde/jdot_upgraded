<?php
/**
 * Mail Template Transport Builder
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace RLTSquare\OrderSendFromFix\Mail\Template;

use Magento\Framework\Mail\MessageInterface;

/**
 * @api
 */
class TransportBuilderByStore extends \Magento\Framework\Mail\Template\TransportBuilderByStore
{

    /**
     * Set mail from address by store.
     *
     * @param string|array $from
     * @param string|int $store
     *
     * @return \Magento\Framework\Mail\Template\TransportBuilderByStore
     */
    public function setFromByStore($from, $store)
    {
        $this->message->clearFrom();
        $this->message->setFrom("eshop@junaidjamshed.com", "J. Junaid Jamshed");

        return $this;
    }
}
