<?php

namespace App\Admin\Repositories;

use App\Models\DeliveryPrice as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class DeliveryPrice extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
