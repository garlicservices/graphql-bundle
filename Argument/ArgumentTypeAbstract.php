<?php

namespace Garlic\GraphQL\Argument;

use Youshido\GraphQL\Type\InputObject\AbstractInputObjectType;
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;

class ArgumentTypeAbstract extends AbstractInputObjectType
{
    /** @var array  */
    private $fields;
    private $name;
    private $description;

    /**
     * AbstractType constructor.
     * @param array $fields
     * @param string $name
     * @param string $description
     */
    public function __construct(array $fields, string $name, string $description)
    {
        $this->fields = $fields;
        $this->name = $name;
        $this->description = $description;

        parent::__construct();
    }

    /**
     * Build Apartment type
     * Fields that showed when user make a query
     *
     * @param ObjectTypeConfig $config
     */
    public function build($config)
    {
        $config->addFields($this->fields);
    }

    /**
     * Name of type that will represent Type in documentation
     *
     * @return bool|string
     */
    public function getName()
    {
        return $this->name.'Type';
    }

    /**
     * Type description that will show in documentation
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}