<?php

namespace App\Admin\Repositories;

use App\Models\CarftSkill as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class CarftSkill extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
