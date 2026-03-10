<?php

declare(strict_types=1);

namespace Drupal\drupal_advanced\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

#[Constraint(
  id: 'PasswordPolicyConstraint',
  label: new TranslatableMarkup('Password Policy Constraint', [], ['context' => 'Validation'])
)]
class PasswordPolicyConstraint extends SymfonyConstraint {

  public string $message = 'Invalid password. Password must be: minimum 3 character types, at least 8 characters in length, and at least 1 special character.';
 
}