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
     * Get field by name
     *
     * @param string $name
     * @return mixed
     */
    public function getField(string $name)
    {
        return $this->fields[$name];
    }

    /**
     * Get argument by name
     *
     * @param string $name
     * @return mixed
     */
    public function getArgument(string $name)
    {
        return $this->fields[$name];
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
     * Add single argument with type and list of options
     *
     * @param string $name
     * @param        $type
     * @param array  $options
     * @return $this|BuilderInterface
     */
    public function addArgument(string $name, $type, array $options = [])
    {
        $this->arguments[$name] = array_merge($options, ['type' => $type]);

        return $this;
    }

    /**
     * Remove field from a Type
     *
     * @param array|string $fieldName
     *
     * @return $this|BuilderInterface
     */
    public function removeField($names)
    {
        if(!is_array($names)) {
            $names = [$fieldName];
        }

        foreach ($names as $name) {
            unset($this->$arguments[$name]);
            unset($this->fields[$name]);
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
    public function removeArgument($names)
    {
        if(!is_array($names)) {
            $names = [$fieldName];
        }

        foreach ($names as $name) {
            unset($this->$arguments[$name]);
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
    public function changeOptions(array $options, $fieldName = null, bool $onlyArgument = false)
    {
        if(isset($names)) {
            if (!is_array($names)) {
                $names = [$names];
            }
        } else {
            $names = $this->fields;
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