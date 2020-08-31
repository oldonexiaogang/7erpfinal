<?php

namespace App\Admin\Repositories;

use App\Models\TransitStorageLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TransitStorageLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
