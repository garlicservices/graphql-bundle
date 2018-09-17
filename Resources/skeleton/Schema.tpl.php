<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $namespace ?>\Mutation\MutationType;
use <?= $namespace ?>\Query\QueryType;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Config\Schema\SchemaConfig;

class <?= $class_name ?> extends AbstractSchema
{
    /**
    * Main service schema. Contains mutation and query fields
    *
    * @param SchemaConfig $config
    */
    public function build(SchemaConfig $config)
    {
        $config
        ->setMutation(new MutationType())
        ->setQuery(new QueryType());
    }
}
