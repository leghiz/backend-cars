<?php

namespace App\Message;

class SendEmailMessage
{
    public function __construct(
        private readonly string $email,
        private readonly string $subject,
        private readonly string $code
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}