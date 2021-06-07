<?php
/**
 * @author    Danail Kyosev <ddkyosev@gmail.com>
 * @copyright 2014, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;

class Label extends RequestPartial
{
    protected $required = [
        'LabelTemplate' => '8X4_A4_PDF',
        'Resolution' => null,
    ];

    public function setLabelTemplate($labelTemplate)
    {
        $this->required['LabelTemplate'] = $labelTemplate;

        return $this;
    }

    public function setResolution($resolution)
    {
        $this->required['Resolution'] = $resolution;

        return $this;
    }
}
