<?php
declare(strict_types=1);

namespace App\Service\Mailer;


use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\RawMessage;
use Twig\Environment;

class CustomMailer implements MailerInterface
{
    private Mailer $mailer;

    public function __construct(private Environment $renderer)
    {
        $mailDsn = $_ENV['MAILER_DSN_REAL'];
        $transport = Transport::fromDsn($mailDsn);
        $this->mailer = new Mailer($transport);
    }

    public function send(RawMessage $message, Envelope $envelope = null): void
    {
        // cause this shit needs to be build by ourself TempleatedEmails are not rendered correctly
        // Its dirty but works. We render the mail body to ensure there is a text. Otherwise mail sent leads to exception
        $bodyRender = new BodyRenderer($this->renderer);
        $bodyRender->render($message);

        $this->mailer->send($message, $envelope);
    }
}