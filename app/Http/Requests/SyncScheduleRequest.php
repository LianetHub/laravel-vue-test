<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SyncScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::in(['draft', 'published'])],
            'shifts' => ['present', 'array'],
            'shifts.*' => ['required', 'array'],
            'shifts.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('assigned_shifts', 'id')->where('schedule_id', $this->route('schedule')?->id)],
            'shifts.*.employee_id' => ['required', 'integer', 'exists:employees,id'],
            'shifts.*.template_id' => ['nullable', 'integer', 'exists:shift_templates,id'],
            'shifts.*.date' => ['required', 'date_format:Y-m-d'],
            'shifts.*.start_time' => ['required', 'date_format:H:i'],
            'shifts.*.end_time' => ['required', 'date_format:H:i'],
            'shifts.*.color' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $cells = [];
            foreach ($this->input('shifts') as $index => $shift) {
                if ($shift['start_time'] >= $shift['end_time']) {
                    $validator->errors()->add("shifts.{$index}.end_time", 'Время окончания должно быть позже времени начала.');
                }
                $key = (int) $shift['employee_id'].':'.$shift['date'];
                if (isset($cells[$key])) {
                    $validator->errors()->add("shifts.{$index}.date", 'На эту дату сотруднику уже назначена смена.');
                }
                $cells[$key] = true;
            }
        });
    }
}
