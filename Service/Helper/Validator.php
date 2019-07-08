<?php


namespace Garlic\GraphQL\Service\Helper;


use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var array */
    public $errors = [];

    /**
     * Validator constructor.
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param $error
     */
    public function addError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * Validate entity and gather errors
     *
     * @param $model
     *
     * @return bool
     */
    public function validate($model)
    {
        $errors = $this->validator->validate($model);
        if (($errors->count() > 0)) {

            foreach ($errors as $error) {
                $this->errors[] = ucfirst($error->getPropertyPath()) . ":" . $error->getMessage();
            }

            return false;
        }

        return true;
    }
}