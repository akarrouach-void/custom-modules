<?php

declare(strict_types=1);

namespace Drupal\drupal_advanced\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use \Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class PasswordPolicyConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The password policy validator service.
   */
  public function __construct(
    private readonly \Drupal\password_policy\PasswordPolicyValidator $passwordPolicyValidator
  ) {}

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {

    $password = $value->value ?? '';
    $entity = $this->context->getRoot()->getValue();
    
    $report = $this->passwordPolicyValidator->validatePassword($password, $entity);
    
    if ($report->isInvalid()) {
      $this->context->addViolation($constraint->message);
    }
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('password_policy.validator')
    );
  }

}