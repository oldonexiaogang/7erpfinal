<?php

namespace App\Admin\Repositories;

use App\Models\SoleWorkshopSubscribe as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SoleWorkshopSubscribe extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
