<?php

namespace App\Admin\Repositories;

use App\Models\Dispatch as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Dispatch extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
