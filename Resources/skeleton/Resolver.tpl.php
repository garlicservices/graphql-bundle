<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Garlic\GraphQL\Field\FieldHelperAbstract;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\AbstractType;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\Scalar\IntType;
use <?= $form_full_class_name ?>;
use <?= $serviceFullName ?>;

class <?= $class_name ?> extends FieldHelperAbstract
{
    /**
    * Arguments that used for filtering returned result.
    * Used for validating incoming parameters
    *
    * @param FieldConfig $config
    * @throws \Youshido\GraphQL\Exception\ConfigurationException
    */
    public function build(FieldConfig $config)
    {
        $type = new <?= $form_class_name ?>();
        $config->addArguments(
        <?php if($suffix != 'Find' && $suffix != 'Delete'): ?>
    $type->getArguments()
        <?php else: ?>
    array_merge(
                $type->getArguments(),
                [
                    'limit' => new IntType(),
                    'offset' => new IntType(),
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
    */
    public function resolve($value, array $args, ResolveInfo $info)
    {
    <?php if($suffix == 'Find' || $suffix == 'Delete'): ?>
    $limit = $this->cutArgument('limit', $args);
        $offset = $this->cutArgument('offset', $args);

    <?php endif ?>
    return $this->container
            ->get(<?= $serviceName?>::class)
    <?php if($suffix == 'Find'): ?>
        ->find($args, ['id' => 'desc'], $limit, $offset)
    <?php elseif($suffix == 'Create'): ?>
        ->create($args)
    <?php elseif($suffix == 'Update'): ?>
        ->update($this->cutArgument('id', $arguments), $arguments)
    <?php elseif($suffix == 'Delete'): ?>
        ->delete($args, $limit, $offset)
    <?php endif ?>
    ;
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
        $type = new <?= $form_class_name ?>();
        return new ListType(
            $type->init()
        );
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