<?php

namespace App\Admin\Repositories;

use App\Models\TransitStorageIn as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TransitStorageIn extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
