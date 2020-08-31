<?php

namespace App\Admin\Repositories;

use App\Models\SoleWorkshopSubscribeLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SoleWorkshopSubscribeLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
