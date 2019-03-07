<?= "<?php\n" ?>

namespace <?= $namespace ?>;

<?php foreach ($fields as $field): ?>
use <?= $field['fullName'] ?>;
<?php endforeach; ?>
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

class <?= $class_name ?> extends AbstractObjectType
{
    <?php foreach ($fields as $field): ?>
/** @var <?= $field['shortName'] ?> */
    private $<?= lcfirst($field['shortName']) ?>;
    <? if(next($fields)) echo "\n"; ?>
    <?php endforeach; ?>

    /**
     * MytationType dependencies setter.
     * @required
     *
<?php foreach ($fields as $field): ?>
     * @param <?= $field['shortName'] ?> $<?= lcfirst($field['shortName']) ?>

<?php endforeach; ?>
     */
    public function setDependencies(
<?php foreach ($fields as $field): ?>
        <?= $field['shortName'] ?> $<?= lcfirst($field['shortName']) ?><? if(next($fields)) echo ','; ?>

<?php endforeach; ?>
    ) {
<?php foreach ($fields as $field): ?>
        $this-><?= lcfirst($field['shortName']) ?> = $<?= lcfirst($field['shortName']) ?>;
<?php endforeach; ?>
    }

    /**
     * Main Mutation type
     * Contains root fields of service
     *
     * @param ObjectTypeConfig $config
     */
    public function build($config)
    {
        $config->addFields(
            [
    <?php foreach ($fields as $field): ?>
            $this-><?= lcfirst($field['shortName']) ?>,
    <?php endforeach; ?>
        ]
        );
    }

    /**
     * Return description which will be represented on documentation page
     *
     * @return string
     */
    public function getDescription()
    {
        return "Main mutation level. Represents all mutation fields of whole service.";
    }
}
