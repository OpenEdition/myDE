<?php

namespace MyDigitalEnvironment\MyDigitalEnvironmentBundle\Service;

use MyDigitalEnvironment\MyDigitalEnvironmentBundle\Entity\User;

interface RegistrationModifierInterface
{
    public function modify(User $user): void;
}