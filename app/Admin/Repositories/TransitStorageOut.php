<?php

namespace App\Admin\Repositories;

use App\Models\TransitStorageOut as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TransitStorageOut extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
