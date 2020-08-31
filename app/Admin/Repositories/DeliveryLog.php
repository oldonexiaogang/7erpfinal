<?php

namespace App\Admin\Repositories;

use App\Models\DeliveryLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class DeliveryLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
