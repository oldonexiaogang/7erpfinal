<?php

namespace App\Admin\Repositories;

use App\Models\InjectMoldDispatchPaper as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class InjectMoldDispatchPaper extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
