<?php
/**
 * @author    Vitalij Rudyuk <rvansp@gmail.com>
 * @copyright 2014
 */
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;

class DutiableQuot extends RequestPartial
{
    protected $required = [
        'DeclaredCurrency' => null,
        'DeclaredValue' => null
    ];

    public function setDeclaredValue($declaredValue)
    {
        $this->required['DeclaredValue'] = $declaredValue;

        return $this;
    }

    public function setDeclaredCurrency($declaredCurrency)
    {
        $this->required['DeclaredCurrency'] = $declaredCurrency;

        return $this;
    }
}
