<?php

namespace App\Admin\Repositories;

use App\Models\RawMaterialStorageLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class RawMaterialStorageLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
