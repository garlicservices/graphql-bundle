<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Garlic\GraphQL\Service\Abstracts\AbstractCrudService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use <?= $entityFullName ?>;

class <?= $class_name ?> extends AbstractCrudService
{
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

        $result = $this->em
            ->getRepository('<?= $entityFullName ?>')
            ->findBy($arguments, $orderBy, $limit, $offset)
        ;

        return $result;
    }

    /**
    * Create new <?= $entityName ?>
    * Necessary to return listable result (array)
    *
    * @param array $arguments
    * @return array
    * @throws \Doctrine\ORM\Mapping\MappingException
    */
    public function create(array $arguments)
    {
        $entity = $this->hydrate(new <?= $entityName ?>(), $arguments);

        $this->em->persist($entity);
        $this->em->flush();

        return [$entity];
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
            throw new NotFoundHttpException("<?= $entityName ?> with input arguments is not found.");
        }

        foreach ($entities as &$entity) {
            $entity = $this->hydrate($entity, $values);
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
            throw new NotFoundHttpException("<?= $entityName ?> list with input arguments is not found.");
        }

        foreach ($entities as $entity) {
            $this->em->remove($entity);
        }

        $this->em->flush();

        return $entities;
    }
}
