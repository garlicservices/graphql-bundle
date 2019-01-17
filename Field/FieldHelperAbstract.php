<?php

namespace Garlic\GraphQL\Field;

use Youshido\GraphQLBundle\Field\AbstractContainerAwareField;

abstract class FieldHelperAbstract extends AbstractContainerAwareField
{
    /**
     * Get argument and delete them from list of incoming arguments
     *
     * @param $name
     * @param $args
     * @return bool
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
}