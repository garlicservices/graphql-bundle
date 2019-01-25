<?php

namespace Garlic\GraphQL\Service\Traits;

use Symfony\Component\Validator\Validation;

trait ValidateTrait
{
    private $errors = [];

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    public function validate($model)
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