<?php

namespace App\Http\Queries\App;

use App\Models\Message;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MessageQuery
{
    private Builder $query;
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->query = Message::with(['user.projectRoles', 'parentMessage.user']);
    }

    public function forProject(int $projectId): self
    {
        $this->query->where('project_id', $projectId);
        return $this;
    }

    public function applySorting(): self
    {
        $this->query->latest();
        return $this;
    }

    public function paginate()
    {
        $this->applySorting();
        $perPage = $this->request->get('per_page', 15);
        return $this->query->paginate($perPage);
    }

    public function find(int $id): ?Message
    {
        return $this->query->findOrFail($id);
    }
}
