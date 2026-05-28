<?php

declare(strict_types=1);

namespace App\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class CustomAction
{
    public static function newButton(string $name, ?string $label = null, ?string $icon = null): Action
    {
        return Action::new($name, $label, $icon)
            ->addCssClass('btn')
            ->displayAsButton();
    }
}
