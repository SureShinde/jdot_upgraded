<?php
/**
 * @author    Vitalij Rudyuk <rvansp@gmail.com>
 * @copyright 2014
 */
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;

class Notification extends RequestPartial
{
    protected $required = [
        'EmailAddress' => null,
        'Message' => null
    ];

    /**
     * @param string $emailAddress Email address
     */
    public function setEmailAddress($emailAddress)
    {
        $this->required['EmailAddress'] = $emailAddress;

        return $this;
    }

    /**
     * @param string $message Message
     */
    public function setMessage($message)
    {
        $this->required['Message'] = $message;

        return $this;
    }
}
