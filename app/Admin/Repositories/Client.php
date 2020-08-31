<?php

namespace App\Admin\Repositories;

use App\Models\Client as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Client extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
