<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidState extends Constraint
{
    public string $message = 'validator.state.required';

    public function getMessage(): string
    {
        return $this->message;
    }
    public function validatedBy(): string
    {
        return \get_class($this).'Validator';
    }
}