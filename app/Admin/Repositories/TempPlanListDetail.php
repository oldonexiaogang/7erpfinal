<?php

namespace App\Admin\Repositories;

use App\Models\TempPlanListDetail as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TempPlanListDetail extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
