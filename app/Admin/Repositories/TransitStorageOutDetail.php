<?php

namespace App\Admin\Repositories;

use App\Models\TransitStorageOutDetail as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TransitStorageOutDetail extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
