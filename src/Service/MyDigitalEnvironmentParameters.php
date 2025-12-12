<?php

namespace MyDigitalEnvironment\MyDigitalEnvironmentBundle\Service;

use Symfony\Component\Mime\Address;

final readonly class MyDigitalEnvironmentParameters
{
    public function __construct(
        private bool $canSendEmail,
        private ?string $emailDomain,
        private string $emailAddressName,
    )
    {
    }

    public function getAddress(string $localPart = 'noreply'): Address
    {
        return new Address($localPart.'@'.$this->emailDomain, $this->emailAddressName);
    }

    public function canSendEmail(): bool
    {
        return $this->canSendEmail;
    }

    public function getEmailDomain(): ?string
    {
        return $this->emailDomain;
    }

    public function getEmailAddressName(): string
    {
        return $this->emailAddressName;
    }
}