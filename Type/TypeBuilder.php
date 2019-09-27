<?php

namespace Garlic\GraphQL\Type;


use Garlic\GraphQL\Type\Interfaces\BuilderInterface;

class TypeBuilder implements BuilderInterface
{
    /** @var array */
    private $fields = [];

    /** @var array */
    private $arguments = [];

    /**
     * Return type fields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get type object
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Add single fields with type and list of options
     *
     * @param string $name
     * @param        $type
     * @param array  $options
     * @return $this|BuilderInterface
     */
    public function addField(string $name, $type, array $options = [])
    {
        $this->fields[$name] = array_merge($options, ['type' => $type]);
        if (!isset($options['argument']) || $options['argument'] !== false) {
            $this->arguments[$name] = array_merge($options, ['type' => $type]);
        }

        return $this;
    }

    /**
     * Remove field from a Type
     *
     * @param array|string $fieldName
     * @param bool         $onlyArgument
     *
     * @return $this|BuilderInterface
     */
    public function removeField($fieldName, bool $onlyArgument = false)
    {
        if(!is_array($fieldName)) {
            $names = [$fieldName];
        }

        foreach ($names as $name) {
            unset($this->$arguments[$name]);
            if (false === $onlyArgument) {
                unset($this->fields[$name]);
            }
        }

        return $this;
    }

    /**
     * Change field options
     *
     * @param string|array $fieldName
     * @param array $options
     * @param bool $onlyArgument
     * @return $this|mixed
     */
    public function changeOptions($fieldName, array $options, bool $onlyArgument = false)
    {
        if(!is_array($fieldName)) {
            $names = [$fieldName];
        }

        foreach ($names as $name) {
            $this->$arguments[$name] = array_merge_recursive($this->$arguments[$name], $options);
            if (false === $onlyArgument) {
                $this->fields[$name] = array_merge_recursive($this->fields[$name], $options);
            }
        }

        return $this;
    }
}