<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nome', 'cognome', 'email', 'telefono', 'attivita', 'settore', 'sito_attuale', 'source', 'consented_at'])]
class Lead extends Model
{
    protected function casts(): array
    {
        return ['consented_at' => 'datetime'];
    }
}
