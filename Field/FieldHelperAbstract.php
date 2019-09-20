<?php

namespace Garlic\GraphQL\Field;

use Garlic\GraphQL\Argument\ArgumentTypeAbstract;
use Youshido\GraphQLBundle\Field\AbstractContainerAwareField;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQLExtension\Type\PagingParamsType;
use Youshido\GraphQLExtension\Type\Sorting\SortingParamsType;

abstract class FieldHelperAbstract extends AbstractContainerAwareField
{
    /**
     * Get arguments and delete them from list of incoming arguments.
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
     * Modify arguments to accept list of values.
     *
     * @param array $arguments
     * @return array
     */
    protected function makeMultiple(array $arguments): array
    {
        $result = [];
        foreach ($arguments as $argumentName => $argument) {
            $result[$argumentName] = $argument['type'] = new ListType($argument['type']);
        }

        return $result;
    }

    /**
     * Modify arguments recursively to accept list of values.
     *
     * @param array $arguments
     * @return array
     */
    protected function makeMultipleRecursive(array $arguments): array
    {
        foreach ($arguments as $key => $argument) {
            if (isset($argument['type'])) {
                $result[$key] = $this->makeMultipleCallback($argument['type']);
            }
        }

        return $result ?? [];
    }

    /**
     * Helper function that recursively modifies type to accept list of values.
     *
     * @param $type
     * @return ListType
     */
    private function makeMultipleCallback($type)
    {
        if ($type->fields ?? false) {
            foreach ($type->fields as $key => $field) {
                $fieldType = $field['type'];

                if ($field['type'] instanceof ArgumentTypeAbstract) {
                    $type->fields[$key] = $this->makeMultipleCallback($fieldType);
                }

                if (!$field['type'] instanceof  ListType) {
                    $type->fields[$key] = new ListType($fieldType);
                }
            }
        }

        if (!$type instanceof  ListType) {
            $type = new ListType($type);
        }

        return $type;
    }

    /**
     * Cut and map pagination and sort arguments
     *
     * @param array $args
     * @return array
     */
    protected function getPagingSortArgumentsData(array &$args)
    {
        $pagination = (array)$this->cutArgument('paging', $args);

        return [
            'sort' => (array)$this->cutArgument('sort', $args) ?? ['id' => 'ASC'],
            'limit' => $pagination['limit'] ?? (int)getenv('DEFAULT_RESULT_LIMIT'),
            'offset' => $pagination['offset'] ?? 0,
        ];
    }

    /**
     * Merge paging/sort arguments
     *
     * @param $type
     * @param array $args
     * @return array
     */
    protected function mergePagingSortArguments($type, array $args = [])
    {
        $paging = new PagingParamsType();
        $sorting = new SortingParamsType($type->init(), array_keys($type->getArguments()));
        if(empty($args)) {
            $args = $type->getArguments();
        }

        return array_merge(
            $args,
            [
                'paging' => $paging->getType(),
                'sort' => $sorting->getType(),
            ]
        );
    }
}


