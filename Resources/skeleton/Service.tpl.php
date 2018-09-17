<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use App\Service\Abstracts\AbstractCrudService;
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
        if(empty($limit)) {
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
    * @param int $id
    * @param array $arguments
    * @return array
    * @throws \Doctrine\ORM\Mapping\MappingException
    */
    public function update(int $id, array $arguments)
    {
        /** @var <?= $entityName ?> $entity */
        $entity = $this->em->getRepository('<?= $entityFullName ?>')->find($id);
        if(empty($entity)) {
            throw new NotFoundHttpException("<?= $entityName ?> with ID $id not found.");
        }

        $entity = $this->hydrate($entity, $arguments);

        $this->em->persist($entity);
        $this->em->flush();

        return [$entity];
    }

    /**
    * Delete found <?= $entityName ?> entities
    *
    * @param array $arguments
    * @param null $limit
    * @param null $offset
    * @return array
    */
    public function delete(array $arguments, $limit = null, $offset = null)
    {
        if(empty($limit)) {
            $limit = getenv('DEFAULT_RESULT_LIMIT');
        }

        /** @var <?= $entityName ?> $apartment */
        $entities = $this->em->getRepository('<?= $entityFullName ?>')
            ->findBy($arguments,  [], $limit, $offset);

        if(count($entities) <= 0) {
            throw new NotFoundHttpException("<?= $entityName ?> list with input arguments is not found.");
        }

        foreach ($entities as $entity) {
            $this->em->remove($entity);
        }

        $this->em->flush();

        return $entities;
    }
}