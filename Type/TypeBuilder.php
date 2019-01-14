<?php

namespace Garlic\GraphQL\Type;


use Garlic\GraphQL\Type\Interfaces\BuilderInterface;

class TypeBuilder implements BuilderInterface
{
    /** @var array */
    private $fields = [];
    private $arguments = [];

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

        return $this;
    }

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
     * Create new type argument
     *
     * @param string $name
     * @param        $type
     * @param array  $options
     * @return BuilderInterface
     */
    public function addArgument(string $name, $type, array $options = [])
    {
        $data = array_merge($options, ['type' => $type]);
        $this->arguments[$name] = $data;
        $this->fields[$name] = $data;

        return $this;
    }
}