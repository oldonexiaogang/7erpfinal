<?php

namespace App\Admin\Repositories;

use App\Models\TempPlanList as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TempPlanList extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
