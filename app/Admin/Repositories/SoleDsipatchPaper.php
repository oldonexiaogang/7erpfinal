<?php

namespace App\Admin\Repositories;

use App\Models\SoleDsipatchPaper as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SoleDsipatchPaper extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
