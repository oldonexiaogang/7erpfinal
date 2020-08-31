<?php

namespace App\Admin\Repositories;

use App\Models\ClientCategory as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class ClientCategory extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
