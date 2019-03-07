<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Garlic\GraphQL\Service\Abstracts\AbstractCrudService;
use Garlic\GraphQL\Service\Traits\ValidateTrait;
use <?= $entityFullName ?>;

class <?= $class_name ?> extends AbstractCrudService
{
    use ValidateTrait;

    /**
     * Return <?= $entityName ?> list
     *
     * @param array $arguments
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return object[]
     */
    public function find(array $arguments, $orderBy = null, $limit = null, $offset = null)
    {
        if (empty($limit)) {
            $limit = getenv('DEFAULT_RESULT_LIMIT');
        }

        $repository = $this->em->getRepository('<?= $entityFullName ?>');
        $result = $repository->findBy($arguments, $this->mapSorting($orderBy), $limit, $offset);

        # TODO: Need to use mo efficient method for counting items
        $fullResult = $repository->findBy($arguments);

        return [
            'items' => $result,
            'pagingInfo' => [
                'totalCount' => count($fullResult),
                'limit' => $limit,
                'offset' => $offset,
            ]
        ];
    }

    /**
     * Create new <?= $entityName ?> items
     * Necessary to return listable result (array)
     *
     * @param array $items
     * @return array
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function create(array $items)
    {
        $result = [];
        foreach ($items as $arguments) {
            $entity = $this->hydrate(new <?= $entityName ?>(), $arguments);
            if (!$this->validate($entity)) {
                return [];
            }

            $this->em->persist($entity);
            $this->em->flush();

            $result[] = $entity;
        }
        
        return $result;
    }

    /**
     * Update <?= $entityName ?> object
     * Must return listable result (array)
     *
     * @param array $arguments
     * @param array $values
     * @param null $limit
     * @param int $offset
     * @return array
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function update(array $arguments, array $values, $limit = null, $offset = 0)
    {
        if (empty($limit)) {
            $limit = getenv('DEFAULT_RESULT_LIMIT');
        }

        /** @var <?= $entityName ?> $entity */
        $entities = $this->em->getRepository('<?= $entityFullName ?>')->findBy($arguments, [], $limit, $offset);
        if (empty($entities)) {
            $this->addError("<?= $entityName ?> with input arguments is not found.");
        }

        foreach ($entities as $k => &$entity) {
            $entity = $this->hydrate($entity, $values);
            if (!$this->validate($entity)) {
                unset($entities[$k]);
                continue;
            }

            $this->em->persist($entity);
        }

        $this->em->flush();

        return $entities;
    }

    /**
     * Delete found <?= $entityName ?> entities
     *
     * @param array $arguments
     * @param int|null $limit
     * @param int $offset
     * @return array
     */
    public function delete(array $arguments, $limit = null, $offset = 0)
    {
        if (empty($limit)) {
            $limit = getenv('DEFAULT_RESULT_LIMIT');
        }

        /** @var <?= $entityName ?> $apartment */
        $entities = $this->em->getRepository('<?= $entityFullName ?>')
            ->findBy($arguments, [], $limit, $offset);

        if (count($entities) <= 0) {
            $this->addError("<?= $entityName ?> list with input arguments is not found.");
        }

        foreach ($entities as $entity) {
            $this->em->remove($entity);
        }

        $this->em->flush();

        return $entities;
    }
}
