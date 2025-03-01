<?php

namespace App\Http\Requests\Product;

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
         'title' => 'required|string',
         'description' => 'required|string',
         'preview_image' => 'required|image|mimes:jpeg,png,jpg,gif',
         'price' => 'required|numeric|min:0',
         'old_price' => 'nullable|numeric|min:0',
         'count' => 'required|integer|min:0',
         'category_id' => 'required|integer|exists:categories,id',
      ];
   }

   public function messages()
   {
      return [
         'title.required' => 'Please write the title',
         'title.string' => 'Title must be a string',
         'description.required' => 'Please write the description',
         'description.string' => 'Description must be a string',
         'preview_image.required' => 'Please upload the image',
         'preview_image.image' => 'File must be an image',
         'preview_image.mimes' => 'Supported image formats: jpeg, png, jpg, gif.',
         'price.required' => 'Please enter the price',
         'price.numeric' => 'Price must be a valid number',
         'price.min' => 'Price must be a positive value.',
         'old_price.numeric' => 'Old price must be a valid number',
         'old_price.min' => 'Old price must be a positive value.',
         'count.required' => 'Please enter the count',
         'count.integer' => 'Count must be an integer',
         'count.min' => 'Count must be a positive value.',
         'category_id.integer' => 'Category id must be an integer',
         'category_id.exists' => 'It seems like this category does not exist',
      ];
   }
}
