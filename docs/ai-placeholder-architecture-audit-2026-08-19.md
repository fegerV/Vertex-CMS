# Special Audit: AI-generated / Placeholder Architecture

Date: 2026-08-19

Scope: Laravel routes, controllers, services, jobs, modules, and documentation were inspected for code that appears implemented by name but is fake, partial, dead, duplicated, or dangerous in production. This audit does **not** judge style; it compares promised behavior with actual behavior and route/call usage.

## Method

Commands used:

- `php artisan route:list --except-vendor`
- `find docs -maxdepth 2 -type f -print`
- `rg -n "TODO|FIXME|NotImplemented|not implemented|mock|fake|stub|placeholder|dummy|sample|demo|hardcoded|return \\[|catch \\(|Exception|Throwable|processPayment|payment|generate|queue|dispatch|Storage::fake|local" app modules routes config database resources tests --glob '!node_modules'`
- targeted reads of AI, ecommerce, queue, media, update, module, form, security and route files

## Findings

| # | FILE | FUNCTION / CLASS | WHAT NAME SUGGESTS | WHAT CODE ACTUALLY DOES | REAL USAGE | STATUS |
|---|------|------------------|--------------------|-------------------------|------------|--------|
| 1 | `app/AI/Services/AiDraftService.php` | `AiDraftService::generate()` | Generates AI text/FAQ/CTA/SEO/builder drafts. | Does not call any model/provider. It builds deterministic canned blocks from page title, URI, brand voice and instruction. | Used by `App\AI\Http\Controllers\AiController::chat()` via `/admin/api/ai/chat`. | FAKE |
| 2 | `app/AI/Services/AiProviderRegistry.php` | `AiProviderRegistry::all()` | Provider registry for OpenAI, Anthropic and custom providers. | Only reports settings and capabilities. Provider metadata is not sufficient to perform provider-specific calls. | Used by AI settings endpoints and Site Wizard provider lookup. | PARTIAL |
| 3 | `app/AI/Services/SiteWizardService.php` | `generateSiteStructure()`, `generateSemanticCore()`, `generateArticlePlan()`, `generateArticleContent()`, `generateImagePrompt()` | Provider-neutral AI site generation. | Checks the selected provider, then delegates to `callAiApi()`. The registry lists Anthropic/custom, but the implementation path is OpenAI-compatible HTTP chat logic, so provider neutrality is overstated. | Routed through `/admin/api/ai/wizard/*`. | PARTIAL |
| 4 | `app/AI/Services/SiteWizardService.php` | `generateImage()` | Generate image via DALL-E or similar. | Hardcodes OpenAI `/v1/images/generations`; ignores registry provider abstraction and returns external URLs only, not stored media assets. | Routed through `/admin/api/ai/wizard/generate-image`. | PARTIAL |
| 5 | `app/Services/AI/ContentGenerationService.php` | `generateText()` and wrappers | Production AI content generation. | Directly calls old OpenAI chat completions with default `gpt-3.5-turbo`; no central provider registry, no app AI settings encryption path, no budget/rate enforcement. | Used by legacy `App\Http\Controllers\Api\AIController`. | PARTIAL |
| 6 | `app/Services/AI/ChatBotService.php` | `answerFAQ()` | Dynamic FAQ chatbot. | First answers from hardcoded/config fallback FAQ strings for delivery/payment/return/warranty/contact before AI. | Used by `/admin/api/ai/faq`. | PARTIAL |
| 7 | `app/Services/AI/ChatBotService.php` | `processOrderQuery()` | AI answer about customer order. | References `\App\Models\Ecommerce\Order`, while real order model is `App\Ecommerce\Models\Order`; therefore it cannot find real orders. | No route found in current route list; service method appears unused. | BROKEN |
| 8 | `app/Services/Ai/SupabaseVectorService.php` | `generateEmbedding()` / `generateMockEmbedding()` | Real vector embedding generation. | If OpenAI key is missing, if HTTP fails, or if exception occurs, returns deterministic md5-based pseudo-vector while logging demo-mode/failure. | Used by `EmbeddingService`; RAG routes use KB services. | FAKE |
| 9 | `app/Services/Ai/SupabaseVectorService.php` | `findRelevantChunks()` | Supabase pgvector semantic search. | Falls back to local PHP search when Supabase is unavailable; this may be acceptable, but combined with mock embeddings it can masquerade as semantic search. | Used by `EmbeddingService::findRelevantChunks()` and RAG chat. | PARTIAL |
| 10 | `app/Services/Ai/EmbeddingService.php` | `$apiKey`, `$apiUrl`, `cosineSimilarity()` | Embedding service owns API and local similarity. | API key/url fields are only used for throttling delay; actual generation/search fully delegates to Supabase service. `cosineSimilarity()` is unused. | Used by document processing/RAG. | DEAD |
| 11 | `app/Media/Services/MediaService.php` | `upload()` | Media upload to configured storage disk. | Stores files under `public/uploads/...` using `File` facade and records `disk = public`; it does not use Laravel `Storage` abstraction/S3/MinIO. | Used by admin and media API uploads. | PARTIAL |
| 12 | `app/Media/Services/MediaService.php` | `generateAiData()` | AI-generated media alt/title/keywords. | Comment says simulation; derives alt/title from filename and EXIF only. No AI provider/API call. | Called automatically in `upload()` when image alt is empty. | FAKE |
| 13 | `app/Media/Services/MediaService.php` | `optimizeImage()` | Reliable image optimization. | Calls `$image->optimize()` and catches all exceptions, returning `false` without recording error context. | Used by admin media optimize route. | PARTIAL |
| 14 | `app/Ecommerce/Services/OrderService.php` | `updatePaymentStatus()` | Payment processing/update via gateway. | Only writes `payment_status`, optional transaction id and `paid_at`; no gateway charge/verification/refund call. | Used by admin order payment route. | FAKE |
| 15 | `app/Ecommerce/Models/Payment.php` | `Payment` model | Real payment records/lifecycle. | Model exists, but `OrderService::updatePaymentStatus()` never creates payment rows. | No route/controller usage found in payment update flow. | DEAD |
| 16 | `app/Ecommerce/Services/OrderService.php` | `refund()` | Refund payment/order. | Only changes order status to `refunded`; does not reverse inventory, create refund record, or call payment gateway. | Used by admin order refund route. | FAKE |
| 17 | `app/Http/Controllers/Admin/QueueController.php` | `apiStats()` | Queue statistics API. | Assumes Redis queue keys and, on any exception, returns success with zero queues and `message = Redis connection not available`; hides failures as healthy-ish data. | Routed under `/admin/api/queues/stats`. | FAKE |
| 18 | `app/Http/Controllers/Admin/QueueController.php` | `apiWorkerStatus()` | Detect active queue workers. | Cached Redis ping only; reports `count = 2` whenever Redis responds, not actual workers. | Routed under `/admin/api/queues/workers`. | FAKE |
| 19 | `app/Http/Controllers/Admin/QueueController.php` | `apiClearQueue()` | Clear a named queue. | Always returns failure saying manual implementation required; accepted `queue` parameter is unused for real work. | Routed under `/admin/api/queues/clear`. | STUB |
| 20 | `app/System/Http/Controllers/QueueController.php` | `show()` | Inspect queue jobs. | Pops up to 50 jobs from Redis, which removes them from the queue just to display them. This is destructive inspection. | Routed under `/admin/system/queues/{queue}`. | BROKEN |
| 21 | `app/System/Http/Controllers/QueueController.php` and `app/Http/Controllers/Admin/QueueController.php` | Queue controllers | Single queue monitoring implementation. | Two implementations exist: Blade/system routes and JSON admin API routes, with different assumptions and behavior. | Both are routed in `routes/admin.php`. | DUPLICATE |
| 22 | `app/Services/UpdateService.php` | `checkForUpdates()` | Reliable update server integration. | Defaults to `https://updates.vertexcms.com`; on failure logs and returns “no update available”, hiding unknown/unreachable update state. | Used by admin update controller. | PARTIAL |
| 23 | `app/Services/UpdateService.php` | `createBackup()` | Pre-update backup. | Copies the parent directory of config or `.env` into `config_backup`; no database dump and likely broader/narrower than intended. | Called by `applyUpdate()`. | BROKEN |
| 24 | `app/Services/UpdateService.php` | `downloadUpdate()` / `applyUpdate()` | Safe self-update flow. | No checksum/signature verification; comment says checksum optional. Then copies package files into base path. | Used by admin update controller. | BROKEN |
| 25 | `app/Services/AI/SmartSearchService.php` | Search service | Smart semantic search. | Searches configured Eloquent models/fields with LIKE-like matching and scoring; no vector/LLM semantics in this legacy namespace. | Used by legacy AI search endpoint. | PARTIAL |
| 26 | `app/Services/Notifications/NotificationService.php` | notification service | Multi-channel notifications. | Telegram/email HTTP calls exist, but channel handling is manually hardcoded and returns “Unknown channel” for anything else; no queue/retry abstraction. | Search shows no current route usage beyond service references; likely future integration. | UNKNOWN |
| 27 | `app/Jobs/ProcessImportJob.php` | `processCsv()`, `processXml()`, `processJson()` | Import content/data. | Parses inputs and comments “process each row/item” without persisting domain records. | Queue job class exists; no route/controller dispatch found in current scan. | STUB |
| 28 | `app/Jobs/GenerateExportJob.php` | `handle()` / format generators | Export domain data. | Generates files from constructor-provided array only; not connected to repository/query extraction. | Job class exists; no clear production dispatch in current scan. | PARTIAL |
| 29 | `app/Builder/Services/BlockDefinitionService.php` | Block definition service | Runtime block definition loading. | File starts with “Service class already exists, just needs proper initialization”; this is a code smell, but the actual service needs deeper runtime validation. | Backend registry appears to be used by builder API. | UNKNOWN |
| 30 | `docs/roadmap.md` | AI status text | “AI module” implemented as draft-first foundation. | Documentation honestly states live external LLM SDK integration is ahead, but routes expose AI-looking endpoints; users may overestimate behavior. | Product docs and admin routes. | PARTIAL |

## Cross-cutting observations

- There are two AI stacks: `App\AI\...` for the newer admin AI/wizard flow and `App\Services\AI\...`/`App\Services\Ai\...` for legacy SEO/RAG/chat flows. Some are routed simultaneously, so “AI” can mean deterministic drafts, OpenAI HTTP calls, md5 mock embeddings, or DB LIKE search depending on endpoint.
- Queue monitoring has two controllers and two sets of admin routes. One JSON controller returns mock-like success on Redis failure; the Blade controller destructively pops jobs when showing a queue.
- Ecommerce payment/refund naming is the riskiest mismatch: admin routes suggest payment/refund operations, but actual behavior is status mutation and webhook emission only.
- Media storage is local public filesystem despite disk metadata. This is not fake if local storage is the intended deployment mode, but it is fake relative to S3/MinIO expectations implied by a storage abstraction.
- Several catches convert operational failures into successful/empty responses. This is dangerous because dashboards can show normal state when dependencies are unavailable.

## TOP 20 MOST DANGEROUS AI-GENERATED PROBLEMS

1. **Fake payment success**: `OrderService::updatePaymentStatus()` can mark an order paid without any gateway verification.
2. **Fake refund**: `OrderService::refund()` only changes status to `refunded`; no money movement or payment record.
3. **Destructive queue inspection**: `System\QueueController::show()` pops jobs while “viewing” them.
4. **Queue health lies**: `Admin\QueueController::apiStats()` returns `success: true` and zero sizes when Redis fails.
5. **Fake worker count**: `apiWorkerStatus()` reports two workers based only on Redis ping.
6. **Mock embeddings in production path**: missing/failing OpenAI key silently produces md5 pseudo-vectors.
7. **Filename-based “AI” media metadata**: uploads auto-fill AI data without AI.
8. **Deterministic “AI draft” endpoint**: `/admin/api/ai/chat` returns templated drafts, not provider output.
9. **Provider-neutral AI is overstated**: registry lists Anthropic/custom, but generation code is OpenAI-compatible.
10. **Unsafe updater**: update packages are applied without checksum/signature verification.
11. **Broken pre-update backup**: backup routine does not dump DB and has suspicious config/.env copy semantics.
12. **Update check hides outage**: failed update server calls become “no update available”.
13. **Two queue implementations**: duplicate controllers with inconsistent assumptions increase operator confusion.
14. **Dead payment model**: `Payment` model implies a payment ledger, but admin payment flow bypasses it.
15. **Broken order query chatbot**: uses a non-existent order namespace, so real orders cannot be queried there.
16. **Legacy AI stack duplicates new AI module**: multiple endpoints implement unrelated AI behavior under similar names.
17. **Smart search is not semantic**: legacy search is field matching, not AI/vector search.
18. **Import jobs do not import domain records**: parse methods contain placeholder processing comments.
19. **Image optimize swallows failures**: exceptions become `false` without operational diagnostics.
20. **Local-only media storage**: upload path bypasses Laravel storage disks and can surprise production deployments expecting S3/MinIO.

## Recommended next actions

1. Rename fake/partial methods or add explicit `demo`/`draft` labels in routes and UI until real integrations exist.
2. Gate fake fallbacks behind non-production checks and fail closed in production.
3. Replace ecommerce payment/refund endpoints with gateway-backed operations or mark them manual status changes.
4. Remove destructive queue inspection and unify queue controllers.
5. Disable mock embeddings outside local/testing; surface dependency failures as warnings/errors.
6. Add automated tests that assert no production path returns mock AI/payment/queue data silently.
