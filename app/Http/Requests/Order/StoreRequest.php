<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
   /**
    * Determine if the user is authorized to make this request.
    */
   public function authorize(): bool
   {
      return true;
   }

   /**
    * Get the validation rules that apply to the request.
    *
    * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
    */
   public function rules(): array
   {
      return [
         'items' => 'required|array|min:1',
         'items.*.product_id' => 'required|exists:products,id',
         'items.*.quantity' => 'required|integer|min:1'
      ];
   }

   public function messages()
   {
      return [
         'items.required' => 'The order must contain at least one product.',
         'items.*.product_id.required' => 'Each item must have a valid product ID.',
         'items.*.product_id.exists' => 'Selected product does not exist.',
         'items.*.quantity.required' => 'Each item must have a quantity.',
         'items.*.quantity.min' => 'Quantity must be at least 1.'
      ];
   }
}
