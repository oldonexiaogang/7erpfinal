<?php

namespace App\Admin\Repositories;

use App\Models\BoxLabelDispatchPaper as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class BoxLabelDispatchPaper extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
