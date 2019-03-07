<?php

namespace Garlic\GraphQL\Service\Abstracts;


use Doctrine\ORM\Mapping\Entity;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManagerInterface;

class AbstractCrudService
{
    /** @var EntityManagerInterface */
    protected $em;

    /**
     * ApartmentService constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Hydrate array to entity object
     *
     * @param $entity
     * @param array $arguments
     * @return Entity
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function hydrate($entity, array $arguments)
    {
        foreach ($arguments as $argument => $value) {
            if (is_array($value)) {
                if(isset($this->em->getClassMetadata(get_class($entity))->associationMappings[$argument])) {
                    $value = $this->hydrateRelation($entity, $argument, $value);
                }
            }
            $entity->{"set".Inflector::camelize($argument)}($value);
        }

        return $entity;
    }

    /**
     * Map and hydrate relations
     *
     * @param $entity
     * @param $name
     * @param $value
     * @return Entity
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    private function hydrateRelation($entity, $name, $value)
    {
        $relationClass = $this->em
            ->getClassMetadata(get_class($entity))
            ->getAssociationMapping($name)["targetEntity"]
        ;

        $relation = $entity->{"get".Inflector::camelize($name)}();
        if(in_array('id', array_keys($value))) {
            $relation = $this->em->getRepository($relationClass)->find($value['id']);
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