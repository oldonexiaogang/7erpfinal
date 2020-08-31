<?php

namespace App\Admin\Repositories;

use App\Models\TransitStorage as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TransitStorage extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
