<?php

declare(strict_types=1);

namespace App\Service\Mailer;

use Exception;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class MailTemplateProvider
{
    private $templates;

    public function __construct(
        #[TaggedIterator('mail.template')] iterable $templates
    ) {
        /** @var MailTemplateInterface[] $this->templates */
        $this->templates = $templates;
    }

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