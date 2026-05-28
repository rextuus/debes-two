<?php

declare(strict_types=1);

namespace App\Service\Mailer;

use Exception;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;


readonly class MailTemplateProvider
{
    /**
     * @param iterable<MailTemplateInterface> $templates
     */
    public function __construct(
        #[AutowireIterator('mail.template')] private iterable $templates
    ) {
    }

    /**
     * @throws Exception
     */
    public function getTemplateByIdent(string $ident): ?MailTemplateInterface
    {
        foreach ($this->templates as $handler) {
            if ($handler->getName() === $ident) {
                return $handler;
            }
        }
        throw new Exception("Could not find a mail template with ident: " . $ident);
    }
}