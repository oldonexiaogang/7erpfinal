<?php

namespace App\Admin\Repositories;

use App\Models\DeliveryPaperDetail as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class DeliveryPaperDetail extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
