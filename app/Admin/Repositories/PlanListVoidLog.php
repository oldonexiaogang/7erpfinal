<?php

namespace App\Admin\Repositories;

use App\Models\PlanListVoidLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class PlanListVoidLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
