<?php

namespace App\Admin\Repositories;

use App\Models\SoleWorkshopSubscribePaper as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SoleWorkshopSubscribePaper extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
