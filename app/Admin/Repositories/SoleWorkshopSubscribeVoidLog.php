<?php

namespace App\Admin\Repositories;

use App\Models\SoleWorkshopSubscribeVoidLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SoleWorkshopSubscribeVoidLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
