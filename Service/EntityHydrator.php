<?php

namespace Garlic\GraphQL\Service;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Garlic\GraphQL\Service\Abstracts\AbstractObjectHydrator;

class EntityHydrator extends AbstractObjectHydrator
{
    /**
     * EntityHydrator constructor.
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        parent::__construct($manager);
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
    public function hydrateRelation($object, string $name, $value)
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
}
