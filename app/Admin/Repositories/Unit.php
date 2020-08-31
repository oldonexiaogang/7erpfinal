<?php

namespace App\Admin\Repositories;

use App\Models\Unit as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Unit extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
