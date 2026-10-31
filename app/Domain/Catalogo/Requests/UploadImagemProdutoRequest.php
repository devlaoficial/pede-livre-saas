<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UploadImagemProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'imagem' => ['required', 'file', 'max:4096', 'mimes:jpeg,png,webp,jpg'],
        ];
    }
}
