<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Garlic\GraphQL\Field\FieldHelperAbstract;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\AbstractType;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Doctrine\ORM\EntityManagerInterface;
use Youshido\GraphQLExtension\Type\PaginatedResultType;
use Youshido\GraphQLExtension\Type\PagingParamsType;
use Youshido\GraphQLExtension\Type\Sorting\SortingParamsType;
use <?= $bounded_full_class_name ?>;
use <?= $entityFullName ?>;


class <?= $class_name ?> extends FieldHelperAbstract
{
    /** @var <?= $bounded_class_name ?> */
    private $type;

    /** @var EntityManagerInterface*/
    private $entityManager;

    /**
    * <?= $class_name ?> constructor.
    * @param <?= $bounded_class_name ?> $type
    * @param EntityManagerInterface $entityManager
    */
    public function __construct(<?= $bounded_class_name ?> $type, EntityManagerInterface $entityManager)
    {
        $this->type = $type;
        $this->entityManager = $entityManager;

        return parent::__construct();
    }

    /**
    * Arguments that used for filtering returned result.
    * Used for validating incoming parameters
    *
    * @param FieldConfig $config
    * @throws \Youshido\GraphQL\Exception\ConfigurationException
    */
    public function build(FieldConfig $config)
    {
        $paging = new PagingParamsType();
        $sorting = new SortingParamsType($this->type->init(), array_keys($this->type->getArguments()));

        $config->addArguments(
            array_merge(
                $this->type->getArguments(),
                [
                    'paging' => $paging->getType(),
                    'sort' => $sorting->getType(),
                ]
            )
        );
    }

    /**
    * Resolver method for apartments list.
    * Used for search and filtering data from database
    *
    * @param $value
    * @param array $args
    * @param ResolveInfo $info
    * @return mixed|null
    * @throws \Youshido\GraphQL\Exception\ConfigurationException
    */
    public function resolve($value, array $args, ResolveInfo $info)
    {
        $sort = (array)$this->cutArgument('sort', $args) ?? ['id' => 'ASC'];
        $pagination = (array)$this->cutArgument('paging', $args);
        $limit = $pagination['limit'] ?? (int) getenv('DEFAULT_RESULT_LIMIT');
        $offset = $pagination['offset'] ?? 0;

        return $this->entityManager
            ->getRepository(<?= $entityName ?>::class)
            ->findBy($args, $sort, $limit, $offset);
    }

    /**
    * Return type of the field
    * Map found data to object type. Could be List, Object etc. By default is ListType
    *
    * @return AbstractObjectType|AbstractType
    * @throws \Youshido\GraphQL\Exception\ConfigurationException
    */
    public function getType()
    {
        return new PaginatedResultType($this->type->init());
    }

    /**
    * Name of field in Main <?= ($isMutation) ? "Mutation":"Query" ?> type. If not set will be used class name in snake_letters style
    *
    * @return bool|null|string
    */
    public function getName()
    {
        return '<?= $class_name ?>';
    }

    /**
    * Return description which will represent on documentation page
    *
    * @return string
    */
    public function getDescription()
    {
        return "Model that return <?= $bounded_class_name ?> objects by custom search";
    }
}
