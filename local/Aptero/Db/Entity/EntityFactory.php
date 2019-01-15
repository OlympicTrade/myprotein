<?php
namespace Aptero\Db\Entity;

class EntityFactory
{
    /**
     * @param Entity $entity
     * @return EntityCollection
     */
    public static function collection(Entity $entity)
    {
        return $entity->getCollection();
    }
}