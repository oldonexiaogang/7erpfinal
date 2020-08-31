<?php

namespace App\Admin\Repositories;

use App\Models\ClientSoleInformation as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class ClientSoleInformation extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
