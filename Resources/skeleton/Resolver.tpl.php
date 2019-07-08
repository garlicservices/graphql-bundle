<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Doctrine\ORM\Mapping\MappingException;
use Garlic\GraphQL\Field\FieldHelperAbstract;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\AbstractType;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Garlic\GraphQL\Exceptions\GraphQLQueryException;
use Youshido\GraphQLExtension\Type\PaginatedResultType;
use Youshido\GraphQLExtension\Type\PagingParamsType;
use Youshido\GraphQLExtension\Type\Sorting\SortingParamsType;
use <?= $form_full_class_name ?>;
use <?= $serviceFullName ?>;

class <?= $class_name ?> extends FieldHelperAbstract
{
    /** @var <?= $form_class_name ?> */
    private $type;

    /** @var <?= $serviceName ?> */
    private $service;

    /**
     * <?= $class_name ?> constructor.
     * @param <?= $form_class_name ?> $type
     * @param <?= $serviceName ?> $service
     */
    public function __construct(<?= $form_class_name ?> $type, <?= $serviceName ?> $service)
    {
        $this->type = $type;
        $this->service = $service;

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
    <?php if($suffix != 'Create'): ?>
    $paging = new PagingParamsType();
        $sorting = new SortingParamsType($this->type->init(), array_keys($this->type->getArguments()));

    <?php endif ?>
    $config->addArguments(
    <?php if($suffix == 'Create'): ?>
        ['items' => new ListType($this->type->init(true))]
    <?php elseif($suffix == 'Update'): ?>
        [
                'arguments' => $this->type->init(true, true),
                'values' => $this->type->init(true),
                'paging' => $paging->getType(),
                'sort' => $sorting->getType(),
            ]
        
    <?php else: ?>
        array_merge(
                $this->makeMultiple($this->type->getArguments()),
                [
                    'paging' => $paging->getType(),
                    'sort' => $sorting->getType(),
                ]
            )
    <?php endif ?>
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
     * @throws MappingException
     */
    public function resolve($value, array $args, ResolveInfo $info)
    {
    <?php if($suffix == 'Find'): ?>
    $sort = (array)$this->cutArgument('sort', $args) ?? ['id' => 'ASC'];
    <?php endif ?>
    <?php if($suffix != 'Create'): ?>
$pagination = (array)$this->cutArgument('paging', $args);
        $limit = $pagination['limit'] ?? (int) getenv('DEFAULT_RESULT_LIMIT');
        $offset = $pagination['offset'] ?? 0;
    <?php else: ?>
$items = $this->cutArgument('items', $args);
    <?php endif ?>
    <?php if($suffix == 'Update'): ?>
$arguments = $this->cutArgument('arguments', $args);
        $values = $this->cutArgument('values', $args);
    <?php endif ?>

        $result = $this
            ->service
    <?php if($suffix == 'Find'): ?>
        ->find($args, $sort, $limit, $offset);
    <?php elseif($suffix == 'Create'): ?>
        ->create($items);
    <?php elseif($suffix == 'Update'): ?>
        ->update($arguments, $values, $limit, $offset);
    <?php elseif($suffix == 'Delete'): ?>
        ->delete($args, $limit, $offset);
    <?php endif ?>

        $errors = $this->service->validator->getErrors();
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $info->getExecutionContext()->addError(new GraphQLQueryException($error));
            }
        }

        return $result;
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
    <?php if($suffix == 'Find'): ?>
    return new PaginatedResultType($this->type->init());
    <?php else: ?>
    return new ListType($this->type->init());
    <?php endif ?>
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
        return "Model that <?= strtolower($suffix) ?> <?= $bounded_class_name ?> objects ";
    }
}
