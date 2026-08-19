# Developer Guide

Extending and customizing the Vertex Forms module.

## Architecture Overview

`
modules/
└── vertex-forms/
    ├── src/
    │   ├── Models/
    │   │   ├── Form.php
    │   │   ├── FormField.php
    │   │   ├── FormSubmission.php
    │   │   ├── FormSubmissionValue.php
    │   │   ├── FormVersion.php
    │   │   └── FormAnalytic.php
    │   ├── Services/
    │   │   ├── FormService.php (facade)
    │   │   ├── FormCalculatorEngine.php
    │   │   ├── FormConditionEngine.php
    │   │   ├── FormImportExportService.php
    │   │   └── FormAnalyticsService.php
    │   ├── Repositories/
    │   │   └── EloquentFormRepository.php (implements FormRepositoryInterface)
    │   ├── Contracts/
    │   │   ├── FormRepositoryInterface.php
    │   │   └── CalculatorEngineInterface.php
    │   ├── Controllers/
    │   │   ├── FormController.php (admin CRUD)
    │   │   ├── FormBuilderController.php (Vue SPA entry)
    │   │   ├── FormSubmissionController.php
    │   │   ├── FormAnalyticsController.php
    │   │   ├── FormVersionController.php
    │   │   └── FormPublicController.php (public API)
    │   ├── FieldTypeRegistry.php (single source of truth)
    │   └── VertexFormsServiceProvider.php (DI, routes, views)
    ├── resources/
    │   ├── views/
    │   │   ├── admin/forms/
    │   │   │   ├── index.blade.php
    │   │   │   └── builder.blade.php
    │   │   └── blocks/
    │   │       ├── form.blade.php
    │   │       └── _form-field.blade.php
    │   └── js/ (future)
    ├── routes/
    │   ├── web.php (public)
    │   └── admin.php (protected)
    ├── database/migrations/
    ├── config/forms.php
    ├── module.json
    └── VertexFormsServiceProvider.php
`

## Service Container Bindings

All services registered in VertexFormsServiceProvider:

`php
->app->singleton(FieldTypeRegistry::class);
->app->bind(FormRepositoryInterface::class, EloquentFormRepository::class);
->app->bind(CalculatorEngineInterface::class, FormCalculatorEngine::class);
->app->singleton(FormService::class);
->app->singleton(FormCalculatorEngine::class);
->app->singleton(FormConditionEngine::class);
->app->singleton(FormImportExportService::class);
->app->singleton(FormAnalyticsService::class);
`

Inject via constructor in your classes:

`php
public function __construct(
    private FormRepositoryInterface ,
    private FormCalculatorEngine 
) {}
`

## Adding a New Field Type

1. **Register in FieldTypeRegistry**

`php
// modules/vertex-forms/src/FieldTypeRegistry.php
public const SCHEMAS = [
    // ... existing types
    'rating' => [
        'label' => 'Star Rating',
        'category' => 'basic',
        'icon' => 'star',
        'props' => [
            'name' => ['type' => 'string', 'required' => true, 'pattern' => '/^[a-z_][a-z0-9_]*$/i'],
            'label' => ['type' => 'string', 'required' => true],
            'max' => ['type' => 'integer', 'default' => 5],
            'min' => ['type' => 'integer', 'default' => 1],
            'required' => ['type' => 'boolean', 'default' => false],
        ],
        'defaults' => ['min' => 1, 'max' => 5],
        'validation' => ['integer', 'min:1', 'max:5'],
        'frontend_component' => 'RatingRenderer',
    ],
];
`

2. **Create Vue renderer component** (future builder: esources/js/admin/forms/builder/fields/RatingRenderer.vue)

3. **Create public field partial** (or update _form-field.blade.php switch case)

`lade
@case('rating')
    <div class="flex gap-1" x-data="{ rating: {{  ?? 0 }} }">
        @for( = ['min'];  <= ['max']; ++)
            <button type="button" @click="rating = {{  }}"
                :class="rating >= {{  }} ? 'text-yellow-400' : 'text-gray-300'">
                ★
            </button>
        @endfor
        <input type="hidden" name="{{  }}" x-model="rating">
    </div>
@endcase
`

4. **Add tests** for the field type validation and rendering.

5. **Update documentation** in docs/forms/field-types.md.

## Custom Validation Rules

Add a rule in FormService or as custom validator:

`php
Validator::extend('future_date', function(, , ) {
    return strtotime() > time();
});
`

Or use inline closure:

`php
 = Validator::make(, [
    'checkout_date' => ['required', 'date', function(, , ) {
        if (strtotime() < time()) {
            ('The '..' must be in the future.');
        }
    }],
]);
`

## Extending with Events

Listen to form events in your own service provider:

`php
use Vertex\Forms\Events\FormSubmitted;
use Illuminate\Events\Dispatcher;

public function boot()
{
    Event::listen(
        FormSubmitted::class,
        function () {
            // Log to external service
            ExternalLogger::log('form_submitted', [
                'form_id' => ->form->id,
                'submission_id' => ->submission->id,
            ]);
        }
    );
}
`

## Custom Calculator Functions

Extend FormCalculatorEngine by overriding or adding methods:

`php
class MyCalculatorEngine extends FormCalculatorEngine
{
    protected function evaluateToken(, )
    {
        if ( === 'sqrt') {
             = array_pop();
            return sqrt();
        }
        return parent::evaluateToken(, );
    }
}
`

Then rebind in your service provider:

`php
->app->bind(
    CalculatorEngineInterface::class,
    MyCalculatorEngine::class
);
`

## Overriding Views

Publish views:

`ash
php artisan vendor:publish --provider="Vertex\Forms\VertexFormsServiceProvider" --tag=views
`

This copies views to esources/views/vendor/forms/. Edit there; they override module defaults.

## Overriding Controllers

Extend and override methods:

`php
class MyFormController extends FormController
{
    public function store(Request )
    {
        // custom logic before parent
         = parent::store();
        // custom logic after
        return ;
    }
}
`

Then update routes in outes/admin.php to use your controller.

## Adding Routes

In your module's outes/admin.php you can add custom endpoints:

`php
Route::middleware(['vertex.permission:forms.edit'])
    ->prefix('forms/{form}')
    ->name('forms.custom.')
    ->group(function () {
        Route::post('/custom-action', [FormController::class, 'customAction'])->name('custom');
    });
`

## Queueing Heavy Tasks

Form submission can trigger expensive operations (file virus scan, large image processing, external API calls). Use Laravel queues:

`php
class ProcessFormSubmission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue;
    
    public function handle(FormSubmitted )
    {
        // Heavy work: scan files, send webhooks, update CRM
    }
}
`

Register listener in EventServiceProvider:

`php
protected  = [
    FormSubmitted::class => [
        ProcessFormSubmission::class,
    ],
];
`

## Database Customization

If you need extra columns, use migrations:

`ash
php artisan make:migration add_custom_column_to_form_fields_table --table=form_fields
`

Add column, then update FormField model $fillable and FieldTypeRegistry prop schema.

## Building the Vue Builder

The builder SPA is not yet implemented. When ready:

1. esources/js/admin/forms/builder/FormBuilder.vue – entry
2. ormBuilderStore.js – Pinia store
3. components/ – FieldPalette, CanvasArea, Inspector, Toolbar
4. ields/ – lazy-loaded field renderers for preview iframe

Use Vite for bundling (already configured in main ite.config.js).

## Testing

### Unit Test Example

`php
class FormCalculatorEngineTest extends TestCase
{
    public function test_evaluates_addition()
    {
         = new FormCalculatorEngine();
         = ->evaluate('{a} + {b}', ['a' => 2, 'b' => 3]);
        ->assertEquals(5, );
    }
}
`

### Feature Test Example

`php
public function test_form_submission_creates_record()
{
     = Form::factory()->create();
     = FormField::factory()->create(['form_id' => ->id, 'name' => 'email']);
    
     = ->post("/forms/{->slug}/submit", [
        'email' => 'test@example.com',
    ]);
    
    ->assertJson(['success' => true]);
    ->assertDatabaseHas('form_submissions', [
        'form_id' => ->id,
    ]);
}
`

### Browser Test (Cypress)

`javascript
describe('Form Builder', () => {
  it('can drag field to canvas', () => {
    cy.visit('/admin/forms/1/builder');
    cy.get('[data-field-type="text"]').drag('#canvas');
    cy.get('#canvas').should('contain', 'Text Input');
  });
});
`

## Performance Tips

- Cache FieldTypeRegistry::SCHEMAS – already static, O(1)
- Cache form schema after first load: Cache::remember("form:{}:schema", 3600, fn() => ->load('fields'))
- Use eager loading: Form::with('fields')->find()
- Avoid N+1 in loops: FormSubmission::with(['values.field'])->get()

## Common Pitfalls

- **Missing field name** → validation error, field not saved
- **Duplicate field names** → last one wins, data loss
- **Non-numeric in calculator** → treated as 0, check your formulas
- **Conditional on later field** → won't work (depends_on must be before)
- **File upload exceeds PHP limits** → silent failure, check upload_max_filesize
- **Cache stale after update** → run Cache::forget("form:{}:schema") in FormObserver (future)

## Contributing

When contributing to forms module:

1. Follow PSR-12 coding style (php artisan pint)
2. Add unit tests for new services
3. Add feature tests for new controller endpoints
4. Update documentation (this file) for user-facing changes
5. Ensure backward compatibility (JSON export/import must work across versions)
6. Use contracts (interfaces) for new services to allow swapping implementations

## Release Checklist

Before tagging v1.x:

- [ ] All tests pass (php artisan test)
- [ ] Code style fixed (php artisan pint)
- [ ] Type safety via PHPStan (level 8)
- [ ] Vue components type-checked (ue-tsc --noEmit)
- [ ] Database migrations reviewed
- [ ] No breaking changes in public API
- [ ] Documentation updated
- [ ] Changelog entry added
- [ ] Translations updated (if i18n module active)

## Further Reading

- Laravel Service Container: https://laravel.com/docs/container
- Laravel Events: https://laravel.com/docs/events
- Alpine.js: https://alpinejs.dev/
- Inertia.js: https://inertiajs.com/
- Shunting-yard algorithm: https://en.wikipedia.org/wiki/Shunting-yard_algorithm
