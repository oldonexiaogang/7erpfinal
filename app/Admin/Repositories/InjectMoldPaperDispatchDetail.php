<?php

namespace App\Admin\Repositories;

use App\Models\InjectMoldDispatchPaperDetail as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class InjectMoldPaperDispatchDetail extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
