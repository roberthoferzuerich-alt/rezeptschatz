<?php

namespace App\Livewire;

use App\Services\CookmateImportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class RecipeImport extends Component
{
    use WithFileUploads;

    #[Validate('required|file|max:307200')] // Bis zu 300 MB
    public $file;

    public bool $isProcessing = false;
    public ?array $resultStats = null;
    public ?string $errorMessage = null;

    public function startImport(CookmateImportService $service): void
    {
        $this->validate([
            'file' => 'required|file|max:307200',
        ], [
            'file.required' => 'Bitte wählen Sie eine COOKmate-Exportdatei (.mcb, .zip oder .xml) aus.',
            'file.max' => 'Die Datei darf maximal 300 MB groß sein.',
        ]);

        $this->isProcessing = true;
        $this->errorMessage = null;
        $this->resultStats = null;

        try {
            $path = $this->file->getRealPath();
            $originalName = $this->file->getClientOriginalName();
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($extension, ['mcb', 'zip', 'xml'], true)) {
                $this->errorMessage = 'Bitte wählen Sie eine gültige COOKmate-Datei aus (.mcb, .zip oder .xml).';
                $this->isProcessing = false;
                return;
            }

            // Temporäre Datei mit Original-Endung vorbereiten, damit ZipArchive sie erkennt
            $tempPath = tempnam(sys_get_temp_dir(), 'cookmate_') . '.' . $extension;
            copy($path, $tempPath);

            $stats = $service->import($tempPath, Auth::user());
            @unlink($tempPath);

            $this->resultStats = $stats;
            session()->flash('success', "Import erfolgreich: {$stats['recipes_count']} Rezepte und {$stats['images_count']} Bilder wurden importiert!");
        } catch (\Throwable $e) {
            $this->errorMessage = 'Fehler beim Import: ' . $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    public function render()
    {
        return view('livewire.recipe-import');
    }
}

