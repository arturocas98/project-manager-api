<?php

namespace App\Http\Controllers;

use App\Actions\App\Incidence\CreateCommentAction;
use App\Actions\App\Incidence\DeleteCommentAction;
use App\Actions\App\Incidence\UpdateCommentAction;
use App\Http\Queries\App\GetCommentsByIncidenceQuery;
use App\Http\Requests\App\CommentRequest;
use App\Http\Requests\App\UpdateCommentRequest;
use App\Http\Resources\App\CommentCollection;
use App\Http\Resources\App\CommentResource;
use DeleteCommentRequest;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Group('App')]
#[Subgroup('IncidenceComent')]
#[Authenticated]
class IncidenceComentController extends Controller
{
    public function __construct(
        private readonly GetCommentsByIncidenceQuery $getCommentsQuery,
        private readonly CreateCommentAction $createAction,
        private readonly UpdateCommentAction $updateAction,
        private readonly DeleteCommentAction $deleteAction
    ) {}

    /**
     * Display a listing of comments for a specific incidence.
     */
    // Controlador
    public function index(int $project, int $incidence): CommentCollection
    {
        $comments = $this->getCommentsQuery->execute($incidence, $project);

        // Pasamos el projectId al Resource Collection
        return (new CommentCollection($comments))
            ->additional(['project_id' => $project]);
    }

    /**
     * Store a newly created comment.
     */
    public function store(CommentRequest $request, int $project, int $incidence): JsonResponse
    {
        $comment = $this->createAction->execute(
            $request->validated()['description'],
            $incidence
        );

        $comment->project_id = $project;
        $comment->load([
            'createdBy',
            'createdBy.projectRoles' => function ($query) use ($project) {
                $query->where('project_id', $project)
                    ->select('project_roles.*');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => new CommentResource($comment),
            'message' => 'Comment created successfully'
        ], 201);
    }

    /**
     * Update the specified comment.
     */
    public function update(UpdateCommentRequest $request, int $project, int $incidence): JsonResponse
    {
        $comment = $this->updateAction->execute(
            $request->validated()['id'],
            $request->validated()['description'],
            $incidence
        );

        $comment->project_id = $project;
        $comment->load([
            'createdBy',
            'createdBy.projectRoles' => function ($query) use ($project) {
                $query->where('project_id', $project)
                    ->select('project_roles.*');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => new CommentResource($comment),
            'message' => 'Comment updated successfully'
        ], 200);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(int $project, int $incidence, int $comentId): JsonResponse
    {
        $this->deleteAction->execute(
            $comentId,
            $incidence
        );

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ], 200);
    }
}