<?php

namespace Vertex\Forms\Controllers;

use App\Http\Controllers\Controller;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormVersion;
use Vertex\Forms\Services\FormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormVersionController extends Controller
{
    public function __construct(
        private readonly FormService $formService,
    ) {}

    public function index(Form $form): JsonResponse
    {
        $versions = $form->versions()->with("user:id,name")->orderBy("version_number", "desc")->get();

        return response()->json([
            "versions" => $versions->map(fn ($v) => [
                "id" => $v->id,
                "version_number" => $v->version_number,
                "comment" => $v->comment,
                "user" => $v->user->name ?? "System",
                "created_at" => $v->created_at?->toDateTimeString(),
            ]),
        ]);
    }

    public function store(Request $request, Form $form): JsonResponse
    {
        $validated = $request->validate([
            "comment" => ["nullable", "string", "max:500"],
        ]);

        $version = $this->formService->createSnapshot(
            $form,
            $validated["comment"] ?? null,
            $request->user()?->id
        );

        return response()->json(["version" => $version], 201);
    }

    public function restore(Request $request, Form $form, FormVersion $version): JsonResponse
    {
        abort_unless((int) $version->form_id === (int) $form->id, 404);

        $this->formService->restoreVersion($form, $version, $request->user()?->id);

        return response()->json(["success" => true, "message" => "Version restored"]);
    }
}
