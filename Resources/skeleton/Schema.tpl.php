<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $namespace ?>\Mutation\MutationType;
use <?= $namespace ?>\Query\QueryType;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Config\Schema\SchemaConfig;

class <?= $class_name ?> extends AbstractSchema
{
    /**
    * @var QueryType
    */
    private $queryType;

    /**
    * @var MutationType
    */
    private $mutationType;

    /**
    * Schema constructor.
    * @param QueryType $queryType
    * @param MutationType $mutationType
    */
    public function __construct(QueryType $queryType, MutationType $mutationType)
    {
        $this->queryType = $queryType;
        $this->mutationType = $mutationType;

        return parent::__construct();
    }

    /**
    * Main service schema. Contains mutation and query fields
    *
    * @param SchemaConfig $config
    */
    public function build(SchemaConfig $config)
    {
        $config
            ->setQuery($this->queryType)
            ->setMutation($this->mutationType);
    }
}
