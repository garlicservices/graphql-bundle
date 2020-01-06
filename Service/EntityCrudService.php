<?php


namespace Garlic\GraphQL\Service;

use Doctrine\ORM\EntityManagerInterface;
use Garlic\GraphQL\Service\Abstracts\AbstractCrudService;

class EntityCrudService extends AbstractCrudService
{
    /** @var EntityManagerInterface */
    protected $manager;

    /**
     * EntityCrudService constructor.
     * @param EntityHydrator         $hydrator
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;        
        $this->hydrator = new EntityHydrator($manager);
    }
}
