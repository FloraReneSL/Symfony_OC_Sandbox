<?php
// src/OC/PlatformBundle/Validator/AntifloodValidator.php

namespace OC\PlatformBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AntifloodValidator extends ConstraintValidator
{
 public function validate($value, Constraint $constraint)
 {
  if(strlen($value) < 3){
   $this->context
      ->buildViolation($constraint->message)
      ->setParameters(array('%string%' => $value))
      ->addViolation()
     ;
  }
 }
}

 ?>
