<?php

namespace App\Admin\Repositories;

use App\Models\SoleWorkshopSubscribeDetail as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SoleWorkshopSubscribeDetail extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
