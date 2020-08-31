<?php

namespace App\Admin\Repositories;

use App\Models\DeliveryLogDetail as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class DeliveryLogDetail extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
