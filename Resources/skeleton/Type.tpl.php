<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Garlic\GraphQL\Type\Interfaces\BuilderInterface;
use Garlic\GraphQL\Type\TypeAbstract;
<?php if (!empty($bounded_full_class_name)): ?>
<?php foreach ($uses as $use): ?>
use Youshido\GraphQL\Type\Scalar\<?=$use ?>;
<?php endforeach; ?>
<?php else: ?>
use Youshido\GraphQL\Type\Scalar\IdType;
<?php endif ?>

<?php if (!empty($bounded_full_class_name)): ?>
use <?= $bounded_full_class_name ?>;
<?php endif ?>

class <?= $class_name ?> extends TypeAbstract
{
    /**
    * Build <?= $class_name ?> fields
    * Will be able to use for config incoming arguments for queries or mutations
    *
    * @param BuilderInterface $builder
    */
    public function build(BuilderInterface $builder)
    {
        $builder
    <?php if (count($formFields) > 1): ?>
    <?php foreach ($formFields as $formField): ?>
        ->addField( '<?= $formField['fieldName'] ?>', new <?= $formField['fieldType']?>()<?php if (!empty($formField['fieldRequired'])): ?>, ['required' => '<?= ($formField['fieldRequired']) ? 'true':'false'?>', 'groups' => 'create'] <?php endif ?>)
    <?php endforeach; ?>
    <?php else: ?>
        ->addField( 'id', new IdType(), ['required' => 'false', 'groups' => 'create'] )
    <?php endif ?>
    ;
    }
    <?php if (!empty($bounded_full_class_name)): ?>
    /**
    * {@inheritdoc}
    */
    public function getEntity()
    {
        return <?=$bounded_class_name?>::class;
    }
    <?php endif ?>
}
