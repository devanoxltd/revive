<?php

namespace App\Support;

use App\Actions\ElaborateSummary;
use App\Actions\FixCode;
use App\Contracts\Tool;

class Pint extends Tool
{
    public function lint(): int
    {
        $this->heading('Linting using Pint');

        return $this->process();
    }

    public function fix(): int
    {
        $this->heading('Fixing using Pint');

        return $this->process();
    }

    private function process(): int
    {
        $fixCode = resolve(FixCode::class);
        $elaborateSummary = resolve(ElaborateSummary::class);

        [$totalFiles, $changes] = $fixCode->execute();

        return $elaborateSummary->execute($totalFiles, $changes);
    }
}
