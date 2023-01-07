<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidState extends Constraint
{
    public string $message = 'Le département {{ state }} n\'existe pas';
    public function validatedBy(): string
    {
        return \get_class($this).'Validator';
    }
}