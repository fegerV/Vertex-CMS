<?php

namespace Vertex\Forms\Controllers;

use App\Http\Controllers\Controller;
use Vertex\Forms\Models\Form;
use Vertex\Forms\FieldTypeRegistry;
use Illuminate\View\View;

class FormBuilderController extends Controller
{
    /**
     * Show the visual form builder (Vue SPA).
     */
    public function show(Form $form): View
    {
        $form->load("fields");

        return view("forms::admin.forms.builder", [
            "form" => $form,
            "fieldTypes" => FieldTypeRegistry::getAll(),
            "fieldCategories" => FieldTypeRegistry::getByCategory(),
        ]);
    }
}
