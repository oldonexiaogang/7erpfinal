<?php

namespace App\Admin\Repositories;

use App\Models\MoldMaker as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class MoldMaker extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
