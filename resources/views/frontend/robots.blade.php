@if(isset($content) && filled($content))
{!! $content !!}
@else
User-agent: *
Allow: /

Sitemap: {{ url('/sitemap.xml') }}
@endif
