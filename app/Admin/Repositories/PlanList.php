<?php

namespace App\Admin\Repositories;

use App\Models\PlanList as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class PlanList extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
