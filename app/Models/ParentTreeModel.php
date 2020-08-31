<?php

namespace App\Models;

use Dcat\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentTreeModel extends Model
{
    use SoftDeletes,
        ModelTree {
        allNodes as treeAllNodes;
        ModelTree::boot as treeBoot;
    }

    protected static function boot()
    {
        static::treeBoot();
    }
    /**
     * Get all elements.
     *
     * @param bool $force
     *
     * @return array
     */
    public function allNodes(bool $force = false): array
    {
        if ($force || $this->queryCallbacks) {
            return $this->fetchAll();
        }
        return $this->fetchAll();
    }
    /**
     * Fetch all elements.
     *
     * @return array
     */
    public function fetchAll(): array
    {
        return $this->withQuery(function ($query) {
            return $query;
        })->treeAllNodes();
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
