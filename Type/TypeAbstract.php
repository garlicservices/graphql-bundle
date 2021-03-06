<?php

namespace Garlic\GraphQL\Type;


use Garlic\GraphQL\Argument\ArgumentTypeAbstract;
use Garlic\GraphQL\Field\FieldAbstract;
use Garlic\GraphQL\Type\Interfaces\BuilderInterface;
use Youshido\GraphQL\Type\ListType\ListType;

abstract class TypeAbstract extends TypeHelper
{
    /** @var TypeBuilder */
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
     * Returns type builder
     *
     * @return TypeBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Create an object depends on is argument required
     *
     * @param bool $argument
     * @return ArgumentTypeAbstract|FieldAbstract
     * @throws \Youshido\GraphQL\Exception\ConfigurationException
     */
    public function init($argument = false, $multiple = false)
    {
        if (!empty($argument)) {
            return $this->getArgumentsType(null, $multiple);
        }

        return $this->getFieldsType($multiple);
    }


    /**
     * Returns Fields Type
     *
     * @param bool $multiple
     * @param array $roles
     * @return FieldAbstract
     * @throws \Youshido\GraphQL\Exception\ConfigurationException
     */
    public function getFieldsType($multiple = false)
    {
        $fields = $this->getFields();
        return new FieldAbstract(
            ($multiple === true) ? new ListType($fields) : $fields,
            $this->getName(),
            $this->getDescription()
        );
    }

    /**
     * Returns Argument Type
     *
     * @param string $actionName
     * @param bool $multiple
     * @param array $roles
     * @return ArgumentTypeAbstract
     * @throws \Youshido\GraphQL\Exception\ConfigurationException
     */
    public function getArgumentsType(string $actionName = null, $multiple = false)
    {
        $arguments = $this->getArguments($actionName, $multiple);
        return new ArgumentTypeAbstract(
            ($multiple === true) ? $this->makeMultiple($arguments) : $arguments,
            $this->getName(),
            $this->getDescription(),
            $multiple
        );
    }

    /**
     * Return array of arguments
     *
     * @param null $groupName
     * @return array
     * @throws \Youshido\GraphQL\Exception\ConfigurationException
     */
    public function getArguments($actionName = null, $multiple = false)
    {
        return $this->setRequired(
            $this->updateRelations($this->builder->getArguments(), true, $multiple),
            $actionName
        );
    }

    /**
     * Return list of arguments
     *
     * @param array $roles
     * @return mixed
     * @throws \Youshido\GraphQL\Exception\ConfigurationException
     */
    public function getFields()
    {
        return $this->updateRelations($this->builder->getFields());
    }

    /**
     * Set required to attributes
     *
     * @param array  $fields
     * @param string $groupName
     * @return array
     * @throws \Youshido\GraphQL\Exception\ConfigurationException
     */
    private function setRequired(array $fields, string $groupName = null): array
    {
        foreach ($fields as $fieldName => $field) {
            if (!empty($field['required'])) {
                if (isset($field['groups']) && !is_array($field['groups'])) {
                    $field['groups'] = [$field['groups']];
                }
                if (empty($field['groups']) || in_array($groupName, $field['groups'])) {
                    $fields[$fieldName] = $this->makeRequired($fields[$fieldName]);
                }
            }
        }

        return $fields;
    }
}