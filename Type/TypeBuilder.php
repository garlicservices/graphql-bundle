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
        if(isset($options['argument']) && $options['argument'] == false) {
            $this->fields[$name] = array_merge($options, ['type' => $type]);
        } else {
            $this->arguments[$name] = array_merge($options, ['type' => $type]);
            $this->fields[$name] = array_merge($options, ['type' => $type]);
        }

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

}