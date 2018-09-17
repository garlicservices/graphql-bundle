<?php

namespace Garlic\GraphQL\Type;


use Garlic\GraphQL\Argument\ArgumentTypeAbstract;
use Garlic\GraphQL\Field\FieldAbstract;
use Garlic\GraphQL\Type\Interfaces\BuilderInterface;

abstract class TypeAbstract extends TypeHelper
{
    /** @var TypeBuilder  */
    private $builder;

    /**
     * TypeAbstract constructor.
     */
    public function __construct()
    {
        $this->builder = new TypeBuilder();
        $this->build($this->builder);
    }

    /**
     * Returns array of type fields
     *
     * @param BuilderInterface $builder
     */
    abstract public function build(BuilderInterface $builder);

    /**
     * Returns related Entity name
     *
     * @return string
     */
    abstract public function getEntity();
    /**
     * Create an object depends on is argument required
     *
     * @param bool $argument
     * @return ArgumentTypeAbstract|FieldAbstract
     * @throws \Youshido\GraphQL\Exception\ConfigurationException
     */
    public function init($argument = false)
    {
        if(!empty($argument)) {
            return new ArgumentTypeAbstract(
                $this->getArguments(),
                $this->getName(),
                $this->getDescription()
            );
        }

        return new FieldAbstract(
            $this->getFields(),
            $this->getName(),
            $this->getDescription()
        );
    }

    /**
     * Return array of arguments
     *
     * @param null $groupName
     * @return array
     * @throws \Youshido\GraphQL\Exception\ConfigurationException
     */
    public function getArguments($groupName = null)
    {
        return $this->setRequired(
            $this->updateRelations($this->builder->getFields(), true),
            $groupName
        );
    }

    /**
     * Return list of arguments
     *
     * @param null $groupName
     * @return array
     * @throws \Youshido\GraphQL\Exception\ConfigurationException
     */
    public function getFields($groupName = null)
    {
        return $this->setRequired(
            $this->updateRelations($this->builder->getFields()),
            $groupName
        );
    }

    /**
     * Get fields by group
     *
     * @param array $fields
     * @param string $groupName
     * @return array
     * @throws \Youshido\GraphQL\Exception\ConfigurationException
     */
    private function setRequired(array $fields, string $groupName = null): array
    {
        if(empty($groupName)) {
            return $fields;
        }

        foreach ($fields as $fieldName => $field) {
            if(!empty($field['required']) && !empty($field['groups'])) {
                if(!is_array($field['groups'])) {
                    $field['groups'] = [$field['groups']];
                }

                if(in_array($groupName, $field['groups'])) {
                    $fields[$fieldName] = $this->makeRequired($fields[$fieldName]);
                }
            }
        }

        return $fields;
    }
}