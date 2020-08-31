<?php

namespace App\Admin\Repositories;

use App\Models\ClientModel as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class ClientModel extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
