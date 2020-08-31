<?php

namespace App\Admin\Repositories;

use App\Models\MoldInformationVoidLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class MoldInformationVoidLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
