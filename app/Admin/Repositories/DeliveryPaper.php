<?php

namespace App\Admin\Repositories;

use App\Models\DeliveryPaper as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class DeliveryPaper extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
