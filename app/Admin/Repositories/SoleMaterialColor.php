<?php

namespace App\Admin\Repositories;

use App\Models\SoleMaterialColor as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SoleMaterialColor extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;

   
   
}
