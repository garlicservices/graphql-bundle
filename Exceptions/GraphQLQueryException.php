<?php


namespace Garlic\GraphQL\Exceptions;


class GraphQLQueryException extends \Exception
{
    /**
     * GraphQLQueryException constructor.
     * @param $message
     * @param $code
     */
    public function __construct($message, $code = 500)
    {
        $this->message = $message;
        if(null == $message) {
            $this->message = "GrapghQL Query Error";
        }

        $this->code = $code;
    }
}