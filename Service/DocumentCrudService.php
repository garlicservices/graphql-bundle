<?php

namespace Garlic\GraphQL\Service;

use Garlic\GraphQL\Service\Abstracts\AbstractCrudService;
use Doctrine\ODM\MongoDB\DocumentManager;

class DocumentCrudService extends AbstractCrudService
{
    /** @var DocumentManager */
    protected $hydrator;

    /** @var DocumentManager */
    protected $manager;

    /**
     * DocumentCrudService constructor.
     * @param DocumentHydrator $hydrator
     * @param DocumentManager  $manager
     */
    public function __construct(DocumentManager $manager)
    {
        $this->manager = $manager;
        $this->hydrator = new DocumentHydrator($manager);
    }
}
