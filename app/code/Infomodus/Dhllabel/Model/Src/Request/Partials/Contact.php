<?php
/**
 * @author    Danail Kyosev <ddkyosev@gmail.com>
 * @copyright 2014, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;

class Contact extends RequestPartial
{
    protected $required = [
        'PersonName' => null,
        'PhoneNumber' => null,
        'Email' => null
    ];

    /**
     * @param string $personName Contact's name
     */
    public function setPersonName($personName)
    {
        $this->required['PersonName'] = $personName;

        return $this;
    }

    /**
     * @param string $phoneNumber Contact's phone number
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->required['PhoneNumber'] = $phoneNumber;

        return $this;
    }

    /**
     * @param string $email Contact's email
     */
    public function setEmail($email)
    {
        $this->required['Email'] = $email;

        return $this;
    }
}
