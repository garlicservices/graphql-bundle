<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Garlic\GraphQL\Type\Interfaces\BuilderInterface;
use Garlic\GraphQL\Type\TypeAbstract;
<?php foreach ($uses as $use): ?>
use Youshido\GraphQL\Type\Scalar\<?=$use ?>;
<?php endforeach; ?>
use <?= $bounded_full_class_name ?>;

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
    <?php foreach ($formFields as $formField): ?>
        ->addField( '<?= $formField['fieldName'] ?>', new <?= $formField['fieldType']?>()<?php if (!empty($formField['fieldRequired'])): ?>, ['required' => '<?= ($formField['fieldRequired']) ? 'true':'false'?>', 'group' => 'create'] <?php endif ?>)
    <?php endforeach; ?>
    ;
    }

    /**
    * {@inheritdoc}
    */
    public function getEntity()
    {
        return <?=$bounded_class_name?>::class;
    }
}