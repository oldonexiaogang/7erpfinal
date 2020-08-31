<?php

namespace App\Admin\Repositories;

use App\Models\PurchaseStandard as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class PurchaseStandard extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
