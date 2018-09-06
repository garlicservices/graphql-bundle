<?php

namespace Garlic\GraphQL\Field;

use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;

class FieldAbstract extends AbstractObjectType
{
    /** @var array  */
    private $fields;

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /**
     * AbstractType constructor.
     * @param array $fields
     * @param $name
     * @param $description
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
        return $this->name;
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