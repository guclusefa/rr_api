<?php

namespace App\Validator;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Repository\StateRepository;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

#[\Attribute]
class ValidStateValidator extends ConstraintValidator
{
public function __construct(private readonly StateRepository $stateRepository)
    {
    }
    public function validate($value, Constraint $constraint): void
    {
        // check if value is a string
        $state = $this->stateRepository->findOneBy(['id' => $value->getId()]);
        if (!$state) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ state }}', $value->getId())
                ->addViolation();
        }
    }
}