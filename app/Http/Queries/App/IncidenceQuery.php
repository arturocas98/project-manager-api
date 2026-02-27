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

    public function byType(int $typeId): self
    {
        $this->query->where('incidence_type_id', $typeId);
        return $this;
    }

    public function byDateRange(?string $startDate, ?string $endDate): self
    {
        if ($startDate) {
            $this->query->where('start_date', '>=', $startDate);
        }
        if ($endDate) {
            $this->query->where('due_date', '<=', $endDate);
        }
        return $this;
    }

    public function byDueDate(?string $dueDate): self
    {
        if ($dueDate) {
            $this->query->whereDate('due_date', $dueDate);
        }
        return $this;
    }

    public function overdue(): self
    {
        $this->query->where('due_date', '<', now())
            ->where('incidence_state_id', '!=', 3); // Excluir cerradas
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

    public function orderByDueDate(string $direction = 'asc'): self
    {
        $this->query->orderBy('due_date', $direction);
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

    public function create(array $data): Incidence
    {
        return Incidence::create($data);
    }

    public function find(int $id): ?Incidence
    {
        return Incidence::find($id);
    }

    public function update(int $id, array $data): bool
    {
        $incidence = $this->find($id);
        if (!$incidence) {
            return false;
        }
        return $incidence->update($data);
    }
}
