<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RecipeImageController extends Controller
{
    /**
     * Liefert das geschützte Rezeptbild aus.
     */
    public function show(Request $request, Recipe $recipe): BinaryFileResponse|Response
    {
        $user = $request->user();

        // Zugriff erlauben, wenn das Rezept dem Benutzer gehört oder geteilt ist
        if ($recipe->user_id !== $user->id && !$recipe->is_shared) {
            abort(403, 'Zugriff verweigert.');
        }

        if (!$recipe->image_path || !Storage::disk('local')->exists($recipe->image_path)) {
            abort(404, 'Bild nicht gefunden.');
        }

        $fullPath = Storage::disk('local')->path($recipe->image_path);

        $headers = [
            'Cache-Control' => 'private, max-age=86400, must-revalidate',
        ];

        if ($recipe->image_hash) {
            $headers['ETag'] = '"' . $recipe->image_hash . '"';
            if ($request->header('If-None-Match') === '"' . $recipe->image_hash . '"') {
                return response('', 304, $headers);
            }
        }

        $fileResponse = response()->file($fullPath, $headers);
        $fileResponse->setPrivate();

        return $fileResponse;
    }
}
