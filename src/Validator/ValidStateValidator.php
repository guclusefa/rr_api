<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Repository\StateRepository;

#[\Attribute]
class ValidStateValidator extends ConstraintValidator
{
    public function __construct
    (
        private readonly StateRepository $stateRepository
    )
    {
    }
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }
        // check if value is a string
        $state = $this->stateRepository->findOneBy(['id' => $value->getId()]);
        if (!$state) {
            $this->context->buildViolation($constraint->getMessage())
                ->setParameter('{{ state }}', $value->getId())
                ->addViolation();
        }
    }
}