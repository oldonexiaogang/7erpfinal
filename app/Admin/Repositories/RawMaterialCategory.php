<?php

namespace App\Admin\Repositories;

use App\Models\RawMaterialCategory as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class RawMaterialCategory extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
