<?php

namespace App\Http\Queries\App;

use App\Models\Incidence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class IncidenceQuery
{
    private Builder $query;

    public function __construct()
    {
        $this->query = Incidence::query();
    }

    public function byProject(int $projectId): self
    {
        $this->query->where('project_id', $projectId);

        return $this;
    }

    public function withDefaultRelations(): self
    {
        $this->query->with([
            'incidenceType',
            'incidenceState',
            'createdBy:id,name,email',
            'assignedUser:id,name,email',
            'parentIncidence:id,title',
        ]);

        return $this;
    }

    public function orderByLatest(): self
    {
        $this->query->orderBy('created_at', 'desc');

        return $this;
    }

    public function get(): Collection
    {
        return $this->query->get();
    }

    public function paginate(int $perPage = 15)
    {
        return $this->query->paginate($perPage);
    }

    /**/
    public function create(array $data): Incidence
    {
        return Incidence::create($data);
    }

    // Nuevo método para encontrar por ID
    public function find(int $id): ?Incidence
    {
        return Incidence::find($id);
    }
}
