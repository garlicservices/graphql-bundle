<?php

namespace Garlic\GraphQL\Field;

use Youshido\GraphQLBundle\Field\AbstractContainerAwareField;
use Youshido\GraphQL\Type\ListType\ListType;

abstract class FieldHelperAbstract extends AbstractContainerAwareField
{
    /**
     * Get argument and delete them from list of incoming arguments
     *
     * @param $name
     * @param $args
     * @return array|null
     */
    protected function cutArgument($name, &$args)
    {
        if (isset($args[$name])) {
            $result = $args[$name];
            unset($args[$name]);
            return $result;
        }

        return null;
    }

    /**
     * Make argument as list
     *
     * @param array $arguments
     * @return array
     */
    protected function makeMultiple(array $arguments)
    {
        $result = [];
        foreach ($arguments as $argumentName => $argument) {
            $result[$argumentName] = $argument['type'] = new ListType($argument['type']);
        }

        return $result;
    }
}