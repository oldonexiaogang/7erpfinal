<?php

namespace App\Admin\Repositories;

use App\Models\SoleWorkshopSubscribeCheckLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SoleWorkshopSubscribeCheckLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
