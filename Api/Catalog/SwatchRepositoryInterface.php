<?php
namespace Bkademy\Webpos\Api\Catalog;

interface SwatchRepositoryInterface
{
    /**
     *
     * @param
     * @return \Bkademy\Webpos\Api\Data\Catalog\SwatchResultInterface
     */
    public function getList();

}
