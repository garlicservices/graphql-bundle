<?php

namespace Garlic\GraphQL\Field;

use Garlic\GraphQL\Argument\ArgumentHelper;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQLBundle\Field\AbstractContainerAwareField;
use Youshido\GraphQLExtension\Type\PagingParamsType;
use Youshido\GraphQLExtension\Type\Sorting\SortingParamsType;

abstract class FieldHelperAbstract extends AbstractContainerAwareField
{
    /** @var  ArgumentHelper */
    private $argumentHelper;

    /**
     * Modify arguments to accept list of values.
     * @param array $arguments
     * @return array
     * @deprecated Use Garlic\GraphQL\Argument\ArgumentHelper
     */
    protected function makeMultiple(array $arguments): array
    {
        return $this->getArgumentHelper()->makeMultiple($arguments);
    }

    /**
     * Modify arguments recursively to accept list of values.
     * @param array $arguments
     * @return array
     * @deprecated Use Garlic\GraphQL\Argument\ArgumentHelper
     */
    protected function makeMultipleRecursive(array $arguments): array
    {
        return $this->getArgumentHelper()->makeMultipleRecursive($arguments);
    }

    /**
     * Helper function that recursively modifies type to accept list of values.
     * @param $type
     * @return ListType
     * @deprecated Use Garlic\GraphQL\Argument\ArgumentHelper
     */
    private function makeMultipleCallback($type)
    {
        return $this->getArgumentHelper()->makeMultipleCallback($type);
    }

    /**
     * Cut and map pagination and sort arguments
     * @param array $args
     * @return array
     * @deprecated Use Garlic\GraphQL\Argument\ArgumentHelper
     */
    protected function getPagingSortArgumentsData(array &$args)
    {
        return $this->getArgumentHelper()->getPagingSortArgumentsData($args);
    }

    /**
     * Merge paging/sort arguments
     * @param       $type
     * @param array $args
     * @return array
     * @deprecated Use Garlic\GraphQL\Argument\ArgumentHelper
     */
    protected function mergePagingSortArguments($type, array $args = [])
    {
        return $this->getArgumentHelper()->mergePagingSortArguments($type, $args);
    }

    /**
     * @return ArgumentHelper
     */
    private function getArgumentHelper()
    {
        if (is_null($this->argumentHelper)) {
            $this->argumentHelper = new ArgumentHelper();
        }

        return $this->argumentHelper;
    }
}
