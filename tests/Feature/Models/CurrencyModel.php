<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyModel extends Model
{
    protected $table = 'currencies';

    protected $guarded = [];

    public $timestamps = false;
}
