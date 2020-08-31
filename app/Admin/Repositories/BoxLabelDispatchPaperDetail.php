<?php

namespace App\Admin\Repositories;

use App\Models\BoxLabelDispatchPaperDetail as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class BoxLabelDispatchPaperDetail extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
