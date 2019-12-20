<?php

namespace Garlic\GraphQL\Argument;

use Garlic\GraphQL\Field\FieldHelperAbstract;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQLBundle\Field\AbstractContainerAwareField;
use Youshido\GraphQLExtension\Type\PagingParamsType;
use Youshido\GraphQLExtension\Type\Sorting\SortingParamsType;

class ArgumentHelper
{
    /**
     * Get arguments and delete them from list of incoming arguments.
     *
     * @param $name
     * @param $args
     * @return array|null
     */
    public function cutArgument($name, &$args)
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
    public function makeMultiple(array $arguments): array
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
    public function makeMultipleRecursive(array $arguments): array
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
    public function makeMultipleCallback($type)
    {
        if ($type->fields ?? false) {
            foreach ($type->fields as $key => $field) {
                $fieldType = $field['type'];
                if ($fieldType instanceof ArgumentTypeAbstract) {
                    $type->fields[$key] = $this->makeMultipleCallback($fieldType);
                }
                if ($fieldType instanceof ListType) {
                    $this->makeMultipleCallback($fieldType->getItemType());
                } else {
                    $type->fields[$key] = new ListType($fieldType);
                }
            }
        }
        if ($type instanceof  ListType) {
            $this->makeMultipleCallback($type->getItemType());
        } else {
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
    public function getPagingSortArgumentsData(array &$args)
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
    public function mergePagingSortArguments($type, array $args = [])
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
