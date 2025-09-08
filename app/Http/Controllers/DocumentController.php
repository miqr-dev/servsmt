<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use App\Document;
use App\DocumentVariable;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $bundeslaender = Document::distinct()->pluck('bundesland');
        return view('documents.index', compact('bundeslaender'));
    }

    public function showVariables($bundesland)
    {
        $documents = Document::where('bundesland', $bundesland)->get();
        $variables = [];

        foreach ($documents as $document) {
            foreach ($document->variables as $variable) {
                if (!isset($variables[$variable->key])) {
                    $variables[$variable->key] = $variable->value;
                }
            }
        }

        return view('documents.edit', compact('variables', 'bundesland'));
    }

public function update(Request $request)
{
    $variables = $request->except('_token', 'bundesland');
    $bundesland = $request->input('bundesland');
    $documents = Document::where('bundesland', $bundesland)->get();
    $documentLinks = [];

    foreach ($documents as $document) {
        // Save or update the variables in the database
        foreach ($variables as $key => $value) {
            $documentVariable = DocumentVariable::where('document_id', $document->id)
                                                ->where('key', $key)
                                                ->first();

            if ($documentVariable) {
                // Update the existing variable
                $documentVariable->value = $value;
                $documentVariable->save();
            } else {
                // Create a new variable if it doesn't exist
                DocumentVariable::create([
                    'document_id' => $document->id,
                    'key' => $key,
                    'value' => $value
                ]);
            }
        }

        // Generate the PDF with updated variables
        $allVariables = $this->getAllVariables($document->name);
        $mergedVariables = array_merge($allVariables, $variables);
        $view = 'documents.' . $document->name;
        $pdf = PDF::loadView($view, $mergedVariables);
        $pdfPath = 'public/pdfs/' . $document->name . '.pdf';
        Storage::put($pdfPath, $pdf->output());
        $documentLinks[$document->city][] = Storage::url($pdfPath); // Group links by city
    }

    return view('documents.links', compact('documentLinks'));
}


    private function getAllVariables($documentName)
    {
        $document = Document::where('name', $documentName)->first();
        $variables = $document->variables->pluck('value', 'key')->toArray();
        return $variables;
    }
}
