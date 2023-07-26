<?php

namespace App\Service\Transaction\Transaction\Form;

use DateTime;

class TransactionCreateLegacyImportData extends TransactionCreateData
{
    private DateTime $created;
    private DateTime $edited;

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    public function getEdited(): DateTime
    {
        return $this->edited;
    }

    public function setEdited(DateTime $edited): void
    {
        $this->edited = $edited;
    }
}
