<?php

namespace App\Admin\Repositories;

use App\Models\DispatchVoidLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class DispatchVoidLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
