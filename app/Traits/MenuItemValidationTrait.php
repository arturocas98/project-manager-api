<?php

namespace App\Traits;

use App\Models\Link;
use Illuminate\Validation\Rule;

trait MenuItemValidationTrait
{
    protected function menuItemRules()
    {
        return [
            'link_id' => [
                'required',
                'integer',
                Rule::exists(Link::class, 'id'),
            ],
            'sequence' => [
                'required',
                'integer',
                'gt:0',
            ],
            'links' => [
                'nullable',
                'array',
            ],
        ];
    }

    protected function validateRecursive($validator, $links, $prefix = 'links', $flatLinks = [])
    {
        $sequences = [];
        foreach ($links as $i => $link) {
            $level =  $prefix . '.' . $i;
            if (in_array($link['link_id'], $flatLinks)) {
                $validator->errors()->add(
                    $level . '.link_id',
                    'Link id should be unique'
                );
            }
            if (in_array($link['sequence'], $sequences)) {
                $validator->errors()->add(
                    $level . '.sequence',
                    'Sequence should be unique'
                );
            }
            $flatLinks[] = $link['link_id'];
            $sequences[] = $link['sequence'];
            $validatorLink = \Illuminate\Support\Facades\Validator::make($link, $this->menuItemRules());
            if ($validatorLink->fails()) {
                foreach ($validatorLink->errors()->messages() as $field => $error) {
                    $validator->errors()->add(
                        $level . '.' . $field,
                        $error
                    );
                }
            }
            if (isset($link['links'])) {
                self::validateRecursive($validator, $link['links'], $level, $flatLinks);
            }
        }
    }
}
