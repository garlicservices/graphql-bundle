<?php

namespace Garlic\GraphQL\Service;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\Mapping\Entity;
use Garlic\GraphQL\Service\Abstracts\AbstractObjectHydrator;

class DocumentHydrator extends AbstractObjectHydrator
{
    /**
     * DocumentHydrator constructor.
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        parent::__construct($documentManager);   
    }

    /**
     * @param        $object
     * @param string $name
     * @param        $value
     * @return Entity
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function hydrateRelation($object, string $name, $value)
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