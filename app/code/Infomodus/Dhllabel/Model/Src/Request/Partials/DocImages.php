<?php
namespace Infomodus\Dhllabel\Model\Src\Request\Partials;
/**
 * @author    Vitalij Rudyuk <rvansp@gmail.com>
 * @copyright 2015
 */
class DocImages extends RequestPartial
{
    protected $required = ['DocImage' => [
        'Type' => null,
        'Image' => null,
        'ImageFormat' => null
    ]
    ];

    public function setType($type)
    {
        $this->required['DocImage']['Type'] = $type;
        return $this;
    }
    public function setImage($image)
    {
        $this->required['DocImage']['Image'] = $image;
        return $this;
    }
    public function setImageFormat($imageFormat)
    {
        $this->required['DocImage']['ImageFormat'] = $imageFormat;
        return $this;
    }
}
