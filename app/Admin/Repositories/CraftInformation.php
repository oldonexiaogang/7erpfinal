<?php

namespace App\Admin\Repositories;

use App\Models\CraftInformation as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class CraftInformation extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
