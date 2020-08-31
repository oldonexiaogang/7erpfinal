<?php

namespace App\Admin\Repositories;

use App\Models\InjectMoldPrice as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class InjectMoldPrice extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
