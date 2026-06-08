<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(
        private readonly MailerInterface $mailer
    ) {
    }

    public function __invoke(SendEmailMessage $message): void
    {
        $email = (new Email())
            ->from('mamamamowna@yandex.ru')
            ->to($message->getEmail())
            ->subject($message->getSubject())
            ->html(sprintf(
                '<p>Здравствуйте!</p><p>Код подтверждения: <strong>%s</strong></p>',
                $message->getCode()
            ));

        $this->mailer->send($email);
    }
}