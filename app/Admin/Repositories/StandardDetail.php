<?php

namespace App\Admin\Repositories;

use App\Models\StandardDetail as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class StandardDetail extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
