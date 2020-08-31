<?php

namespace App\Admin\Repositories;

use App\Models\Color as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Color extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
