<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($entries as $entry)
    <url>
        <loc>{{ $entry['loc'] }}</loc>
        <lastmod>{{ $entry['lastmod']?->toAtomString() }}</lastmod>
        @if(isset($entry['priority']))
        <priority>{{ number_format($entry['priority'], 1) }}</priority>
        @endif
        @if(isset($entry['changefreq']))
        <changefreq>{{ $entry['changefreq'] }}</changefreq>
        @endif
    </url>
@endforeach
</urlset>
