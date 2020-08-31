<?php

namespace App\Admin\Repositories;

use App\Models\DeliveryDetail as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class DeliveryDetail extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
