<?php

namespace App\Admin\Repositories;

use App\Models\DeliveryVoidLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class DeliveryVoidLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
