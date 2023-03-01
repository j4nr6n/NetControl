<?php

namespace App\MessageHandler;

use App\Message\EmailVerification;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Mime\Address;

#[AsMessageHandler]
class EmailVerificationHandler
{
    public function __construct(
        readonly private MailerInterface $mailer,
        readonly private UserRepository $userRepository
    ) {
    }

    public function __invoke(EmailVerification $userRegistration): void
    {
        $user = $this->userRepository->find($userRegistration->getUserId());

        if (!$user) {
            throw new UnrecoverableMessageHandlingException(sprintf(
                'Unknown user "%s". It may have been deleted already.',
                $userRegistration->getUserId()
            ));
        }

        try {
            $token = bin2hex(random_bytes(32));
        } catch (\Exception $exception) {
            throw new RecoverableMessageHandlingException('Not enough randomness.', 0, $exception);
        }

        $user
            ->setToken($token)
            ->setEmailVerified(false);
        $this->userRepository->save($user, true);

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@netcontrol.app', 'NetControl'))
            ->to(new Address((string) $user->getEmail()))
            ->subject('NetControl Email Verification')
            ->htmlTemplate('emails/email_verification.html.twig')
            ->context([
                'token' => $token,
            ]);

        $this->mailer->send($email);
    }
}
