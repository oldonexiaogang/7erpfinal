<?php

namespace App\Admin\Repositories;

use App\Models\PlanListDetail as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class PlanListDetail extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
