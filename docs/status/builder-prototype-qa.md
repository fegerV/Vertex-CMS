# Builder Prototype QA

## Goal

Validate the registry-driven Vue/TipTap builder prototype before it is integrated into the main admin builder runtime.

## Checklist

- mount the prototype on an element with `data-vc-page-builder-prototype`
- pass initial blocks through `data-initial-value`
- if a hidden form field is used, verify sync through `data-input-target`
- add several blocks from the registry palette
- delete and reorder blocks with the inline controls
- verify TipTap editing for `paragraph`, `heading`, and `text`
- verify payload updates for `hero`, `cta`, `faq`, `gallery`, `video`, `form-embed`, and `columns`
- verify the `vertex-builder:save` custom event fires on save
- verify malformed initial JSON does not crash the page
