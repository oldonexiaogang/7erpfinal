<?php

namespace App\Admin\Repositories;

use App\Models\CompanyModel as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class CompanyModel extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
