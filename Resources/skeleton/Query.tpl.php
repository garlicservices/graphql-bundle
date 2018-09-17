<?= "<?php\n" ?>

namespace <?= $namespace ?>;

<?php foreach ($fields as $field): ?>
use <?= $field['fullName'] ?>;
<?php endforeach; ?>
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

class <?= $class_name ?> extends AbstractObjectType
{
    /**
    * Main query type
    * Contains root fields of service
    *
    * @param ObjectTypeConfig $config
    */
    public function build($config)
    {
        $config->addFields(
            [
        <?php foreach ($fields as $field): ?>
        new <?= $field['shortName'] ?>(),
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
        return "Main query level. Represents all query fields of whole service.";
    }
}
