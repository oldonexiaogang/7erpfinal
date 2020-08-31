<?php

namespace App\Admin\Repositories;

use App\Models\DliveryPrice as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class DliveryPrice extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
