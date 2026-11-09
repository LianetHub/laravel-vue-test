<?php

namespace App\Http\Requests;

use App\Models\AssignedShift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAssignedShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $presence = $this->route('shift') ? 'sometimes' : 'required';

        return [
            'employee_id' => [$presence, 'integer', 'exists:employees,id'],
            'template_id' => ['nullable', 'integer', 'exists:shift_templates,id'],
            'date' => [$presence, 'date_format:Y-m-d'],
            'start_time' => [$presence, 'date_format:H:i'],
            'end_time' => [$presence, 'date_format:H:i'],
            'color' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $shift = $this->route('shift');
            $start = $this->input('start_time', $shift?->start_time);
            $end = $this->input('end_time', $shift?->end_time);
            if (substr($start, 0, 5) >= substr($end, 0, 5)) {
                $validator->errors()->add('end_time', 'Время окончания должно быть позже времени начала.');
            }
            $query = AssignedShift::where('schedule_id', $shift?->schedule_id ?? $this->route('schedule')?->id)
                ->where('employee_id', $this->input('employee_id', $shift?->employee_id))
                ->whereDate('date', $this->input('date', $shift?->date?->format('Y-m-d')));
            if ($shift) {
                $query->where('id', '!=', $shift->id);
            }
            if ($query->exists()) {
                $validator->errors()->add('date', 'На эту дату сотруднику уже назначена смена.');
            }
        });
    }
}
