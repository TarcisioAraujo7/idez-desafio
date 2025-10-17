<?php

namespace App\Http\Providers;

use Illuminate\Support\Collection;

interface Provider
{
    public function indexMunicipalities(string $uf): Collection;

    public function generateUrl(string $uf): string;
}
