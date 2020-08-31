<?php

namespace App\Admin\Repositories;

use App\Models\SoleMaterial as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SoleMaterial extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
