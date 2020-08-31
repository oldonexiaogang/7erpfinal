<?php

namespace App\Admin\Repositories;

use App\Models\CraftColor as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class CraftColor extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
