<?php

/*
 * This file is part of Symfony Boilerplate.
 *
 * (c) Saif Eddin Gmati
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Service;

use Psl\Str;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\ExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class Mailer
{
    private const ADDRESS = 'Hello <hello@example.com>';

    private MailerInterface $mailer;

    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function send(Email $email, ?Envelope $envelope = null): void
    {
        try {
            $email->from(Address::fromString(self::ADDRESS));

            $this->mailer->send($email, $envelope);
        } catch (ExceptionInterface $exception) {
            $this->logger->error(Str\format('Error while attempting to send an email: %s', $exception->getMessage()), [
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }
}
