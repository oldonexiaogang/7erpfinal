<?php

namespace App\Admin\Repositories;

use App\Models\RawMaterialStorageOutPaper as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class RawMaterialStorageOutPaper extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
