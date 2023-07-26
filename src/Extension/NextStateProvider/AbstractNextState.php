<?php
declare(strict_types=1);

namespace App\Extension\NextStateProvider;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class AbstractNextState
{
    public const DEBT_POSTFIX = '_debt';
    public const LOAN_POSTFIX = '_loan';

    public function __construct(protected UrlGeneratorInterface $router)
    {
    }
}
