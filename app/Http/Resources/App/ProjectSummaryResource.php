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
        // Generate pagination meta (even though this is a single resource, we follow the standard)
        $meta = [
            'current_page' => 1,
            'from' => 1,
            'last_page' => 1,
            'links' => [
                [
                    'url' => null,
                    'label' => '&laquo; Previous',
                    'active' => false
                ],
                [
                    'url' => $request->url(),
                    'label' => '1',
                    'active' => true
                ],
                [
                    'url' => null,
                    'label' => 'Next &raquo;',
                    'active' => false
                ]
            ],
            'path' => $request->url(),
            'per_page' => 15,
            'to' => 1,
            'total' => 1
        ];

        // Generate links
        $links = [
            'self' => $request->url(),
            'project' => route('projects.show', ['project' => $request->route('project')]),
        ];

        return [
            'data' => $this->resource,
            'meta' => $meta,
            'links' => $links,
        ];
    }
}