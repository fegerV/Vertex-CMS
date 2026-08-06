@extends('admin.layouts.app')

@section('title', 'Социальные сети и Open Graph')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-share-alt text-primary"></i>
                Социальные сети и Open Graph
            </h1>
            <p class="text-muted">Настройте отображение вашего сайта в социальных сетях</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.seo.social.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Open Graph -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fab fa-facebook text-primary"></i> Open Graph (Facebook, VK)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Заголовок по умолчанию</label>
                            <input type="text" name="og_title" class="form-control" value="{{ $ogSettings['og_title'] }}" placeholder="{{ config('app.name') }}">
                            <small class="text-muted">Рекомендуемая длина: 60 символов</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Имя сайта</label>
                            <input type="text" name="og_site_name" class="form-control" value="{{ $ogSettings['og_site_name'] }}" placeholder="{{ config('app.name') }}">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Описание по умолчанию</label>
                    <textarea name="og_description" class="form-control" rows="3" placeholder="Краткое описание вашего сайта">{{ $ogSettings['og_description'] }}</textarea>
                    <small class="text-muted">Рекомендуемая длина: 150-200 символов</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Изображение по умолчанию</label>
                    <div class="input-group">
                        <input type="text" name="og_image" id="ogImage" class="form-control" value="{{ $ogSettings['og_image'] }}" placeholder="https://example.com/image.jpg">
                        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('imageUpload').click()">
                            <i class="fas fa-upload"></i> Загрузить
                        </button>
                    </div>
                    <input type="file" id="imageUpload" class="d-none" accept="image/*" onchange="uploadImage(this)">
                    <small class="text-muted d-block mt-1">Рекомендуемый размер: 1200x630 пикселей</small>
                    
                    @if($ogSettings['og_image'])
                        <div class="mt-2">
                            <img src="{{ $ogSettings['og_image'] }}" alt="OG Image" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Twitter Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fab fa-twitter text-info"></i> Twitter Card</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Тип карточки</label>
                            <select name="twitter_card" class="form-select">
                                <option value="summary" {{ $ogSettings['twitter_card'] === 'summary' ? 'selected' : '' }}>Summary (Маленькая картинка)</option>
                                <option value="summary_large_image" {{ $ogSettings['twitter_card'] === 'summary_large_image' ? 'selected' : '' }}>Summary Large Image (Большая картинка)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Twitter аккаунт сайта</label>
                            <input type="text" name="twitter_site" class="form-control" value="{{ $ogSettings['twitter_site'] }}" placeholder="@username">
                            <small class="text-muted">Например: @vertex_cms</small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Twitter автора</label>
                    <input type="text" name="twitter_creator" class="form-control" value="{{ $ogSettings['twitter_creator'] }}" placeholder="@author_username">
                </div>
            </div>
        </div>

        <!-- Социальные профили -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-link text-success"></i> Профили в социальных сетях</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-facebook text-primary"></i> Facebook Page</label>
                            <input type="url" name="facebook_page" class="form-control" value="{{ $ogSettings['facebook_page'] }}" placeholder="https://facebook.com/yourpage">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Facebook App ID</label>
                            <input type="text" name="facebook_app_id" class="form-control" value="{{ $ogSettings['facebook_app_id'] }}" placeholder="1234567890">
                            <small class="text-muted">Для Facebook Insights</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-vk text-primary"></i> ВКонтакте</label>
                            <input type="url" name="vk_url" class="form-control" value="{{ $ogSettings['vk_url'] }}" placeholder="https://vk.com/yourclub">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-instagram text-danger"></i> Instagram</label>
                            <input type="url" name="instagram_url" class="form-control" value="{{ $ogSettings['instagram_url'] }}" placeholder="https://instagram.com/yourprofile">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-youtube text-danger"></i> YouTube</label>
                            <input type="url" name="youtube_url" class="form-control" value="{{ $ogSettings['youtube_url'] }}" placeholder="https://youtube.com/c/yourchannel">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-telegram text-info"></i> Telegram</label>
                            <input type="url" name="telegram_url" class="form-control" value="{{ $ogSettings['telegram_url'] }}" placeholder="https://t.me/yourchannel">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fab fa-linkedin text-primary"></i> LinkedIn</label>
                            <input type="url" name="linkedin_url" class="form-control" value="{{ $ogSettings['linkedin_url'] }}" placeholder="https://linkedin.com/company/yourcompany">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Предпросмотр -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-eye text-warning"></i> Предпросмотр</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Facebook / VK</h6>
                        <div class="border rounded p-3 bg-light" id="fbPreview">
                            <div style="font-family: Helvetica, Arial, sans-serif;">
                                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                    <div style="width: 40px; height: 40px; background: #ddd; border-radius: 50%; margin-right: 10px;"></div>
                                    <div>
                                        <div style="font-weight: bold; font-size: 14px;">{{ $ogSettings['og_site_name'] }}</div>
                                        <div style="font-size: 12px; color: #666;">vertex-cms.com</div>
                                    </div>
                                </div>
                                <div style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                                    @if($ogSettings['og_image'])
                                        <img src="{{ $ogSettings['og_image'] }}" style="width: 100%; height: auto; max-height: 300px; object-fit: cover;">
                                    @else
                                        <div style="background: #e0e0e0; height: 200px; display: flex; align-items: center; justify-content: center; color: #999;">Нет изображения</div>
                                    @endif
                                    <div style="padding: 12px;">
                                        <div style="font-weight: bold; font-size: 16px; margin-bottom: 4px; color: #1d2129;">{{ $ogSettings['og_title'] }}</div>
                                        <div style="font-size: 14px; color: #606770;">{{ Str::limit($ogSettings['og_description'], 100) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Twitter</h6>
                        <div class="border rounded p-3 bg-light" id="twitterPreview">
                            <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                                <div style="background: white; border: 1px solid #e1e8ed; border-radius: 14px; overflow: hidden;">
                                    @if($ogSettings['og_image'] && $ogSettings['twitter_card'] === 'summary_large_image')
                                        <img src="{{ $ogSettings['og_image'] }}" style="width: 100%; height: auto; max-height: 300px; object-fit: cover;">
                                    @endif
                                    <div style="padding: 12px;">
                                        <div style="font-weight: bold; font-size: 15px; margin-bottom: 2px;">{{ $ogSettings['og_title'] }}</div>
                                        <div style="font-size: 14px; color: #657786;">{{ Str::limit($ogSettings['og_description'], 100) }}</div>
                                        <div style="font-size: 14px; color: #657786; margin-top: 4px;">vertex-cms.com</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Сохранить настройки
            </button>
        </div>
    </form>
</div>

<script>
function uploadImage(input) {
    if (input.files && input.files[0]) {
        const formData = new FormData();
        formData.append('image', input.files[0]);
        
        fetch('{{ route("admin.seo.social.upload-image") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('ogImage').value = data.url;
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>
@endsection
