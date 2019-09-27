<?php

namespace Garlic\GraphQL\Type\Interfaces;


interface BuilderInterface
{
    /**
     * Create new type field
     *
     * @param string $name
     * @param $type
     * @param array $options
     * @return BuilderInterface
     */
    public function addField(string $name, $type, array $options = []);

    /**
     * Get type object
     *
     * @return array
     */
    public function getFields();

    /**
     * Get type object
     *
     * @return array
     */
    public function getArguments();

    /**
     * Remove field from the Type
     *
     * @param string|array $fieldName
     * @return BuilderInterface
     */
    public function removeField($fieldName);

    /**
     * Change field options
     *
     * @param string|array $fieldName
     * @param array $options
     * @param bool $onlyArgument
     * @return mixed
     */
    public function changeOptions(array $options, $fieldName = null, bool $onlyArgument = false);
}