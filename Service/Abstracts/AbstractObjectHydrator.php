<?php

namespace Garlic\GraphQL\Service\Abstracts;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\Entity;

abstract class AbstractObjectHydrator
{
    protected $manager;

    /**
     * ObjectHydrator constructor.
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Hydrate array to entity object
     *
     * @param $object
     * @param array $arguments
     * @return Entity
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function hydrate($object, array $arguments)
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
    abstract public function hydrateRelation($object, string $name, $value);

    /**
     * Make sorting array
     *
     * @param $sort
     * @return array
     */
    public function mapSorting($sort)
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
