<?php

namespace Garlic\GraphQL\Type;


use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\TypeInterface;
use Youshido\GraphQL\Type\ListType\ListType;

class TypeHelper
{
    /**
     * Name of type that will represent current Type in documentation
     *
     * @return bool|string
     */
    public function getName()
    {
        $fullClass = explode('\\', static::class);

        return str_replace('Type', '', end($fullClass));
    }

    /**
     * Type description that will show in documentation
     *
     * @return string
     */
    public function getDescription()
    {
        return 'This type use for represent the ' . static::getName() .'model.';
    }

    /**
     * Update all relation fields to argument or field. Depends on type argument
     *
     * @param $fields
     * @param bool $argument
     * @return mixed
     * @throws \Youshido\GraphQL\Exception\ConfigurationException
     */
    protected function updateRelations(array $fields, $argument = false)
    {
        foreach ($fields as $key => $field){

            /** @var TypeAbstract $value */
            foreach ($field as $name => $value){
                if($name == 'type') {
                    if($value instanceof ListType) {
                        if($value->getItemType() instanceof TypeAbstract ) {
                            $fields[$key][$name] = new ListType($value->getItemType()->init($argument));
                        }
                    }

                    if($value instanceof TypeAbstract){
                        $fields[$key][$name] = $value->init($argument);
                    }
                }

            }
        }

        return $fields;
    }

    /**
     * Make Argument required
     *
     * @param $argument
     * @return array|NonNullType
     * @throws \Youshido\GraphQL\Exception\ConfigurationException
     */
    protected function makeRequired($argument)
    {
        if ($argument instanceof TypeInterface) {
            $argument = new NonNullType($argument);
        } elseif(is_array($argument)) {
            $argument['type'] = new NonNullType($argument['type']);
        }

        return $argument;
    }
}