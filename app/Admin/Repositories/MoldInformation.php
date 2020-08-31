<?php

namespace App\Admin\Repositories;

use App\Models\MoldInformation as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class MoldInformation extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
