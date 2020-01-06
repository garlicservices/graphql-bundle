<?php

namespace Garlic\GraphQL\Service\Abstracts;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\Entity;

/**
 * Class AbstractCrudService
 * @internal use EntityCrudService or DocumentCrudService | for hydration Use DocumentHydrator or EntityHydrator
 */
class AbstractCrudService
{
    /** @var ObjectHydrator */
    protected $hydrator;

    /**
     * Hydrate array to entity object
     *
     * @deprecated use EntityHydrator
     * 
     * @param $object
     * @param array $arguments
     * @return Entity
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function hydrate($object, array $arguments)
    {
        return $this->hydrator->hydrate($object, $arguments);
    }

    /**
     * Map and hydrate relations
     * @deprecated use EntityHydrator
     *
     * @param $object
     * @param $name
     * @param $value
     * @return Entity
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    protected function hydrateRelation($object, string $name, $value)
    {
        return $this->hydrator->hydrateRelation($object, $name, $value);
    }

    /**
     * Make sorting array
     * @deprecated use EntityHydrator
     * 
     * @param $sort
     * @return array
     */
    protected function mapSorting($sort)
    {
        return $this->hydrator->mapSorting($sort);
    }
}
