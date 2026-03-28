<?php

namespace App\Http\Requests\App\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMediaRequest extends FormRequest
{
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json(['message' => 'No cuentas con los permisos para adjuntar archivos a este recurso específico.'], 422)
        );
    }

    public function authorize()
    {
        $modelClass = $this->input('model_type');
        $modelId = $this->input('model_id');

        $modelClass = \Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($modelClass) ?? $modelClass;

        if (!class_exists($modelClass)) {
            return false;
        }

        $model = $modelClass::find($modelId);
        if (!$model) {
            return false;
        }

        $user = $this->user();

        if ($model instanceof \App\Models\User) {
            return $model->id === $user->id;
        }

        if ($model instanceof \App\Models\Project) {
            return $model->hasUserAccess($user->id);
        }

        if ($model instanceof \App\Models\Incidence) {
            return $model->project && $model->project->hasUserAccess($user->id);
        }

        if (isset($model->user_id)) {
            return $model->user_id === $user->id;
        }

        return false;
    }

    public function rules()
    {
        return [
            'model_type' => ['required', 'string'],
            'model_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip,rar', 'max:20480'],
            'collection_name' => ['nullable', 'string'],
        ];
    }
}
