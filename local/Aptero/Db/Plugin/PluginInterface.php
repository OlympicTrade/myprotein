<?php
namespace Aptero\Db\Plugin;

use Aptero\Db\Entity\Entity;

interface PluginInterface
{
    public function rFill($data);

    public function serializeArray();

    public function unserializeArray($data);

    public function setParent(Entity $parent);

    public function save();
}