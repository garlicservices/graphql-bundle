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
}