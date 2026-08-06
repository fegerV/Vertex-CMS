# AI Site Wizard - Vertex CMS

## Overview

The AI Site Wizard is an optional onboarding feature that helps users quickly create a complete website structure using AI. After installing CMS on hosting, users can skip the "blank canvas" problem by letting AI generate:

- Website name and tagline
- Complete page structure with content
- Navigation menu
- Semantic core (keywords)
- Article plans for blog/content sections
- Full article content
- Image prompts and generated images

## Architecture

### Backend Services

#### `SiteWizardService` (`app/AI/Services/SiteWizardService.php`)

Main service handling all AI-powered site creation tasks:

1. **generateSiteStructure()** - Creates complete site structure from description
2. **generateSemanticCore()** - Generates keyword list grouped by intent
3. **generateArticlePlan()** - Creates content plan for sections
4. **generateArticleContent()** - Writes full articles based on outlines
5. **generateImagePrompt()** - Creates detailed prompts for image generation
6. **generateImage()** - Generates images via DALL-E 3
7. **saveSiteStructure()** - Saves generated structure to database

#### `AiController` (`app/AI/Http/Controllers/AiController.php`)

Extended with wizard endpoints:

- `POST /api/ai/wizard/generate-structure` - Generate site structure
- `POST /api/ai/wizard/generate-semantic-core` - Generate keywords
- `POST /api/ai/wizard/generate-article-plan` - Generate article plans
- `POST /api/ai/wizard/generate-article-content` - Generate article content
- `POST /api/ai/wizard/generate-image-prompt` - Generate image prompts
- `POST /api/ai/wizard/generate-image` - Generate images
- `POST /api/ai/wizard/save-structure` - Save structure to database

### Supported AI Providers

The wizard works with multiple providers through `AiProviderRegistry`:

1. **OpenAI** (default)
   - GPT-4o-mini for text generation
   - DALL-E 3 for images
   - Requires: `openai_api_key`

2. **Anthropic**
   - Claude Sonnet for text generation
   - Requires: `anthropic_api_key`

3. **Custom** (OpenAI-compatible API)
   - Any compatible endpoint
   - Requires: `custom_api_base`, `custom_api_key`

## Wizard Flow

### Step 1: Welcome & Provider Selection

```
┌─────────────────────────────────────────┐
│  🎉 Welcome to Vertex CMS!             │
│                                         │
│  Let AI help you create your website   │
│  in minutes instead of hours.          │
│                                         │
│  [Skip Wizard]  [Get Started]          │
└─────────────────────────────────────────┘
```

If user continues:

```
┌─────────────────────────────────────────┐
│  Choose AI Provider                     │
│                                         │
│  ○ OpenAI (GPT-4o, DALL-E 3)           │
│    Status: ✓ Configured                │
│                                         │
│  ○ Anthropic (Claude)                  │
│    Status: ⚠ Not configured            │
│                                         │
│  ○ Custom Provider                     │
│    Status: ⚠ Not configured            │
│                                         │
│  [Configure Keys]  [Continue]          │
└─────────────────────────────────────────┘
```

### Step 2: Describe Your Website

```
┌─────────────────────────────────────────┐
│  Tell us about your website            │
│                                         │
│  What will your site be about?         │
│  ┌───────────────────────────────────┐ │
│  │ I'm opening a small coffee shop   │ │
│  │ called "Morning Brew" in Seattle. │ │
│  │ We specialize in artisanal coffee │ │
│  │ and homemade pastries...          │ │
│  └───────────────────────────────────┘ │
│                                         │
│  Industry/Niche: [Food & Beverage ▼]   │
│  Target Audience: [Local customers,    │
│                    coffee enthusiasts]  │
│  Tone of Voice: [Friendly & Warm ▼]    │
│  Language: [Russian ▼]                 │
│                                         │
│  [Back]  [Generate Structure]          │
└─────────────────────────────────────────┘
```

### Step 3: Review Generated Structure

While generating (30-60 seconds):

```
┌─────────────────────────────────────────┐
│  ✨ AI is creating your website...     │
│                                         │
│  ━━━━━━━━━━━━━━━━░░░░  65%             │
│                                         │
│  • Analyzing your requirements... ✓    │
│  • Creating site structure... ✓        │
│  • Writing page content... ⏳          │
│  • Building navigation menu... ⏳      │
│  • Generating SEO data... ⏳           │
│                                         │
│  This may take up to 2 minutes         │
└─────────────────────────────────────────┘
```

Review results:

```
┌─────────────────────────────────────────┐
│  Site Name: "Morning Brew"             │
│  Tagline: "Artisanal Coffee & Pastries"│
│                                         │
│  Pages Generated (5):                  │
│  ┌─────────────────────────────────┐   │
│  │ ☑ Home                          │   │
│  │ ☑ About Us                      │   │
│  │ ☑ Menu                          │   │
│  │ ☑ Our Story                     │   │
│  │ ☑ Contact                       │   │
│  └─────────────────────────────────┘   │
│                                         │
│  Menu Items:                           │
│  Home | Menu | About | Contact         │
│                                         │
│  [Regenerate]  [Edit]  [Continue]      │
└─────────────────────────────────────────┘
```

### Step 4: Semantic Core (Optional)

```
┌─────────────────────────────────────────┐
│  📊 Semantic Core Generation           │
│                                         │
│  Generate keywords for SEO?            │
│                                         │
│  This will create:                     │
│  • Core keywords (10-15)               │
│  • Long-tail keywords (20-30)          │
│  • Question-based queries (10-15)      │
│  • Commercial intent keywords (10-15)  │
│                                         │
│  [Skip]  [Generate Keywords]           │
└─────────────────────────────────────────┘
```

Results preview:

```
┌─────────────────────────────────────────┐
│  Keywords Generated: 87                │
│                                         │
│  Core Keywords:                        │
│  • кофе Сиэтл                          │
│  • кофейня рядом                       │
│  • авторский кофе                      │
│  • свежая выпечка                      │
│  ...                                   │
│                                         │
│  [Export CSV]  [Add to SEO]  [Skip]    │
└─────────────────────────────────────────┘
```

### Step 5: Content Plan for Blog/Sections

```
┌─────────────────────────────────────────┐
│  📝 Content Plan Generation            │
│                                         │
│  Section: Blog                         │
│  Topic Focus: Coffee culture & tips    │
│                                         │
│  How many articles to plan?            │
│  [5 ▼]                                 │
│                                         │
│  Generated Articles:                   │
│  ┌─────────────────────────────────┐   │
│  │ 1. "Как выбрать правильный кофе"│   │
│  │    Priority: High               │   │
│  │    Words: ~1500                 │   │
│  │                                 │   │
│  │ 2. "5 способов заваривания..."  │   │
│  │    Priority: Medium             │   │
│  │    Words: ~2000                 │   │
│  │    ...                          │   │
│  └─────────────────────────────────┘   │
│                                         │
│  [Regenerate]  [Select All]  [Next]    │
└─────────────────────────────────────────┘
```

### Step 6: Generate Article Content (Optional)

```
┌─────────────────────────────────────────┐
│  ✍️ Article Content Generation         │
│                                         │
│  Selected: "Как выбрать правильный кофе"│
│                                         │
│  Outline:                              │
│  • Введение                            │
│  • Виды кофейных зерен                 │
│  • Степени обжарки                     │
│  • Как хранить кофе                    │
│  • Заключение                          │
│                                         │
│  [ ] Generate full content (~1500 words)│
│  [ ] Generate featured image           │
│                                         │
│  [Skip]  [Generate Selected]           │
└─────────────────────────────────────────┘
```

### Step 7: Image Generation (Optional)

```
┌─────────────────────────────────────────┐
│  🎨 Image Generation                   │
│                                         │
│  For: Homepage Hero Image              │
│                                         │
│  AI Prompt Preview:                    │
│  "Cozy coffee shop interior with warm │
│  lighting, wooden tables, barista     │
│  preparing espresso, steam rising     │
│  from cups, inviting atmosphere,      │
│  professional photography, high       │
│  resolution"                          │
│                                         │
│  Settings:                             │
│  Size: [1024x1024 ▼]                  │
│  Quality: [Standard ▼]                │
│  Variations: [1 ▼]                    │
│                                         │
│  Cost: ~$0.04 per image               │
│                                         │
│  [Edit Prompt]  [Generate Images]      │
└─────────────────────────────────────────┘
```

### Step 8: Final Review & Save

```
┌─────────────────────────────────────────┐
│  ✅ Ready to Create Your Site!         │
│                                         │
│  Summary:                              │
│  • Site Name: Morning Brew             │
│  • Pages: 5                            │
│  • Menu Items: 4                       │
│  • Keywords: 87                        │
│  • Article Plans: 5                    │
│  • Articles Written: 2                 │
│  • Images Generated: 3                 │
│                                         │
│  All content will be saved as drafts.  │
│  You can edit everything later.        │
│                                         │
│  [Go Back]  [Create Site]              │
└─────────────────────────────────────────┘
```

## API Examples

### Generate Site Structure

```bash
curl -X POST https://your-cms.com/api/ai/wizard/generate-structure \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "openai",
    "description": "Интернет-магазин экологичных товаров для дома. Продаем многоразовые альтернативы одноразовым вещам.",
    "niche": "E-commerce, Eco-friendly products",
    "target_audience": "Эко-сознательные потребители 25-45 лет",
    "tone": "friendly, informative",
    "language": "ru"
  }'
```

Response:

```json
{
  "success": true,
  "data": {
    "structure": {
      "site_name": "EcoHome",
      "tagline": "Устойчивый выбор для вашего дома",
      "pages": [
        {
          "title": "Главная",
          "uri": "/",
          "meta_title": "EcoHome - Экологичные товары для дома",
          "meta_description": "Многоразовые альтернативы одноразовым вещам...",
          "content": "<p>Добро пожаловать в EcoHome...</p>",
          "blocks": [...],
          "keywords": ["эко товары", "многоразовое использование"]
        },
        ...
      ],
      "menu": [
        {"title": "Главная", "url": "/"},
        {"title": "Каталог", "url": "/catalog"},
        ...
      ]
    },
    "usage": {
      "prompt_tokens": 450,
      "completion_tokens": 1200,
      "total_tokens": 1650
    }
  }
}
```

### Generate Semantic Core

```bash
curl -X POST https://your-cms.com/api/ai/wizard/generate-semantic-core \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "openai",
    "description": "Сайт студии йоги в Москве",
    "niche": "Fitness, Wellness"
  }'
```

### Generate Article Content

```bash
curl -X POST https://your-cms.com/api/ai/wizard/generate-article-content \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "openai",
    "title": "10 преимуществ утренней йоги",
    "outline": ["Введение", "Польза для тела", "Польза для ума", "Как начать"],
    "keywords": ["утренняя йога", "польза йоги", "йога для начинающих"],
    "tone": "inspirational, friendly",
    "word_count": 1500
  }'
```

### Generate Image

```bash
curl -X POST https://your-cms.com/api/ai/wizard/generate-image \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "Yoga studio interior with natural light, wooden floors, plants, calm atmosphere, professional photography",
    "model": "dall-e-3",
    "size": "1024x1024",
    "quality": "standard",
    "count": 1
  }'
```

### Save Structure to Database

```bash
curl -X POST https://your-cms.com/api/ai/wizard/save-structure \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "structure": {
      "site_name": "EcoHome",
      "pages": [...],
      "menu": [...]
    },
    "options": {
      "menu_name": "Main Navigation"
    }
  }'
```

## Configuration

### Environment Variables

```env
# AI Settings
AI_ENABLED=true
AI_DEFAULT_PROVIDER=openai
AI_DEFAULT_MODEL=gpt-4o-mini

# OpenAI
OPENAI_API_KEY=sk-...

# Anthropic (optional)
ANTHROPIC_API_KEY=sk-ant-...

# Custom Provider (optional)
AI_CUSTOM_API_BASE=https://your-api.com/v1
AI_CUSTOM_API_KEY=your-key
```

### Database Tables Required

- `pages` - Page storage
- `page_contents` - Page content and blocks
- `menus` - Menu definitions
- `menu_items` - Menu items
- `seo_keywords` - (optional) Keyword storage

## Best Practices

### Prompt Optimization

1. **Be specific** - More details = better results
2. **Set context** - Include industry, audience, tone
3. **Iterate** - Allow regeneration if results aren't perfect
4. **Review** - Always review AI content before publishing

### Cost Management

1. **Show estimates** - Display approximate API costs
2. **Allow skipping** - Make each step optional
3. **Batch operations** - Combine requests when possible
4. **Cache results** - Don't regenerate unchanged content

### User Experience

1. **Progress indicators** - Show what's happening during generation
2. **Partial saves** - Allow saving work-in-progress
3. **Easy editing** - Make it simple to modify AI output
4. **Clear CTAs** - Guide users through the flow

## Future Enhancements

- [ ] Multi-language support expansion
- [ ] Integration with stock photo APIs
- [ ] A/B testing for generated content
- [ ] Analytics on wizard completion rates
- [ ] Template library for common site types
- [ ] Collaborative editing of AI-generated content
- [ ] Version history for regenerated content
- [ ] Integration with external CMS migration tools

## Troubleshooting

### Common Issues

**"AI provider not configured"**
- Check API keys in settings
- Verify `AI_ENABLED=true` in config

**"Failed to parse JSON"**
- AI response may have been truncated
- Try regenerating with smaller scope
- Check provider API status

**"Rate limit exceeded"**
- Implement request queuing
- Add delays between batch operations
- Consider upgrading API plan

**Images not generating**
- Verify DALL-E access in OpenAI account
- Check image generation quota
- Ensure prompt meets guidelines

## Security Considerations

1. **API Key Storage** - Store keys encrypted in database/environment
2. **Rate Limiting** - Prevent abuse of AI endpoints
3. **Content Moderation** - Screen generated content for policy violations
4. **User Permissions** - Require appropriate permissions for AI features
5. **Audit Logging** - Track all AI generation requests

---

*Documentation version: 1.0*
*Last updated: 2024*
