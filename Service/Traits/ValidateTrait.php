<?php

namespace Garlic\GraphQL\Service\Traits;

use Symfony\Component\Validator\Validation;

/**
 * Trait ValidateTrait
 *
 * @package Garlic\GraphQL\Service\Traits
 */
trait ValidateTrait
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param $error
     */public function addError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * @param $model
     *
     * @return bool
     */public function validate($model)
    {
        $validator = Validation::createValidator();
        $errors = $validator->validate($model);
        if (($errors->count() > 0)) {
            $this->errors = array_merge($this->errors, $errors);

            return false;
        }

        return true;
    }
}