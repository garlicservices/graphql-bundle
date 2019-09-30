<?php

namespace Garlic\GraphQL\Service\Abstracts;


use Doctrine\ORM\Mapping\Entity;
use Doctrine\Common\Inflector\Inflector;

/**
 * Class AbstractCrudService
 * @internal use EntityCrudService or DocumentCrudService
 */
class AbstractCrudService
{
    /** @var */
    protected $manager;

    /**
     * Hydrate array to entity object
     *
     * @param $object
     * @param array $arguments
     * @return Entity
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function hydrate($object, array $arguments)
    {
        foreach ($arguments as $argument => $value) {
            if (is_array($value)) {
                if(isset($this->manager->getClassMetadata(get_class($object))->associationMappings[$argument])) {
                    $value = $this->hydrateRelation($object, $argument, $value);
                }
            }
            $object->{"set".Inflector::camelize($argument)}($value);
        }

        return $object;
    }

    /**
     * Map and hydrate relations
     *
     * @param $object
     * @param $name
     * @param $value
     * @return Entity
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function hydrateRelation($object, string $name, $value)
    {
        $relationClass = $this->manager
            ->getClassMetadata(get_class($object))
            ->getAssociationMapping($name)["targetEntity"]
        ;

        $relation = $object->{"get".Inflector::camelize($name)}();
        if(in_array('id', array_keys($value))) {
            $relation = $this->manager->getRepository($relationClass)->find($value['id']);
            unset($value['id']);
        } elseif(empty($relation)) {
            $relation = new $relationClass;
        }

        return $this->hydrate($relation, $value);
    }

    /**
     * Make sorting array
     *
     * @param $sort
     * @return array
     */
    protected function mapSorting($sort)
    {
        if(empty($sort)) {
            return [];
        }

        $mapping = [-1 => 'DESC', 1 => 'ASC'];
        return [
            $sort['field'] => $mapping[$sort['order']]
        ];
    }
}