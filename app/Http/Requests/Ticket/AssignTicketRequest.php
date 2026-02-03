<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('ticket.assign');
    }

    public function rules(): array
    {
        return [
            'assigned_to' => [
                'required',
                'exists:users,id',
                Rule::exists('users', 'id')->where(function ($query) {
                    return $query->whereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'technician');
                    });
                }),
            ],
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to.required' => 'Teknisi wajib dipilih',
            'assigned_to.exists' => 'Hanya user dengan role Technician yang dapat di-assign',
            'notes.max' => 'Catatan maksimal 1000 karakter',
        ];
    }
}
