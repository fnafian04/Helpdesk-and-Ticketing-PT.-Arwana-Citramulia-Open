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
                'integer',
                'exists:users,id',
                Rule::exists('users', 'id'),
            ],
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $assignedTo = $this->input('assigned_to');
            
            // Validate user has technician role
            if ($assignedTo) {
                $user = \App\Models\User::find($assignedTo);
                
                if (!$user) {
                    $validator->errors()->add('assigned_to', 'User tidak ditemukan');
                } elseif (!$user->hasRole('technician')) {
                    $validator->errors()->add('assigned_to', 'Hanya user dengan role Technician yang dapat di-assign');
                } elseif (!$user->is_active) {
                    $validator->errors()->add('assigned_to', 'Teknisi harus dalam status aktif');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'assigned_to.required' => 'Teknisi wajib dipilih',
            'assigned_to.exists' => 'Teknisi tidak ditemukan',
            'notes.max' => 'Catatan maksimal 1000 karakter',
        ];
    }
}
