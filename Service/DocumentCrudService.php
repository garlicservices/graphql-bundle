<?php


namespace Garlic\GraphQL\Service;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\MappingException;
use Garlic\GraphQL\Service\Abstracts\AbstractCrudService;

class DocumentCrudService extends AbstractCrudService
{
    /**
     * Map and hydrate relations
     *
     * @param $object
     * @param string $name
     * @param $value
     * @return Entity
     * @throws MappingException
     */
    protected function hydrateRelation($object, string $name, $value)
    {
        $relationClass = $relationClass = $this->manager
            ->getClassMetadata(get_class($object))
            ->getAssociationTargetClass($name);

        $relation = $object->{"get".Inflector::camelize($name)}();
        if (in_array('id', array_keys($value))) {
            $relation = $this->manager->getRepository($relationClass)->find($value['id']);
            unset($value['id']);
        } elseif (empty($relation)) {
            $relation = new $relationClass;
        }

        return $this->hydrate($relation, $value);
    }
}