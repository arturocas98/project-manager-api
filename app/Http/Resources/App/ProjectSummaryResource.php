<?php

namespace App\Http\Resources\App;


use Illuminate\Http\Resources\Json\JsonResource;

class ProjectSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $projectId = $request->route('project');

        return [
            'data' => $this->resource,
            'meta' => [
                'api_version' => '1.0',
                'timestamp' => now()->toIso8601String(),
                'project' => [
                    'id' => $projectId,
                    'url' => route('projects.show', $projectId),
                ],
                'cache' => [
                    'ttl' => 300, // 5 minutos en segundos
                    'stale_at' => now()->addMinutes(5)->toIso8601String(),
                ],
            ],
            'links' => [
                'self' => $request->fullUrl(),
                'project' => route('projects.show', $projectId),
                'incidences' => route('projects.incidences.index', $projectId),
                'members' => route('projects.members.index', $projectId),
            ],
        ];
    }

    /**
     * Customize the response for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function withResponse($request, $response)
    {
        $response->header('X-Project-Summary-Version', '1.0');
        $response->header('X-Generated-At', now()->toIso8601String());
    }
}