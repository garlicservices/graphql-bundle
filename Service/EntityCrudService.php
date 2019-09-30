<?php


namespace Garlic\GraphQL\Service;

use Doctrine\ORM\EntityManagerInterface;
use Garlic\GraphQL\Service\Abstracts\AbstractCrudService;

class EntityCrudService extends AbstractCrudService
{
    /** @var EntityManagerInterface */
    protected $manager;

    /**
     * ApartmentService constructor.
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }
}