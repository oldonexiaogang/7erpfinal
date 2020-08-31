<?php

namespace App\Admin\Repositories;

use App\Models\PlanCategory as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class PlanCategory extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
