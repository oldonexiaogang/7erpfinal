<?php

namespace App\Admin\Repositories;

use App\Models\RawMaterialProductInformation as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class RawMaterialProductInformation extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
