import HeroBlock from './blocks/Hero.vue';
import CtaBlock from './blocks/Cta.vue';
import SchemaBlock from './blocks/SchemaBlock.vue';
import { reactive } from 'vue';

function makeId() {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }

    return `block_${Math.random().toString(36).slice(2, 10)}`;
}

const registryState = reactive({
    loaded: false,
    loading: null,
    remoteEntries: [],
    remoteMap: {},
});

const hiddenExperimentalTypes = new Set(['paragraph', 'form-embed', 'hero', 'cta']);

const experimentalRegistry = {
    paragraph: {
        type: 'paragraph',
        label: 'Paragraph',
        category: 'experimental',
        description: 'Legacy prototype alias for text blocks.',
        component: SchemaBlock,
        paletteHidden: true,
        defaultSettings: () => ({
            content: 'Start writing...',
        }),
    },
    hero: {
        type: 'hero',
        label: 'Hero',
        category: 'experimental',
        description: 'Hero banner with CTA.',
        component: HeroBlock,
        paletteHidden: true,
        defaultSettings: () => ({
            title: 'Hero title',
            subtitle: 'Describe the value of this page.',
            background: '',
            buttonText: 'Learn more',
            buttonUrl: '#',
            buttonTarget: '_self',
            titleColor: '#ffffff',
            subtitleColor: '#e5e7eb',
            buttonBgColor: '#2563eb',
            buttonTextColor: '#ffffff',
            buttonBorderColor: 'transparent',
        }),
    },
    cta: {
        type: 'cta',
        label: 'CTA',
        category: 'experimental',
        description: 'Call-to-action card with title, copy, and button.',
        component: CtaBlock,
        paletteHidden: true,
        defaultSettings: () => ({
            title: 'Ready to start?',
            description: 'Add supporting copy for your call to action.',
            buttonText: 'Contact us',
            buttonUrl: '#',
        }),
    },
    'form-embed': {
        type: 'form-embed',
        label: 'Form Embed',
        category: 'experimental',
        description: 'Legacy prototype alias for the backend form block.',
        component: SchemaBlock,
        paletteHidden: true,
        defaultSettings: () => ({
            form_id: '',
            title: 'Contact form',
            description: '',
        }),
    },
};

function deepMerge(base, incoming) {
    const source = base && typeof base === 'object' && !Array.isArray(base) ? base : {};
    const target = incoming && typeof incoming === 'object' && !Array.isArray(incoming) ? incoming : {};
    const keys = new Set([...Object.keys(source), ...Object.keys(target)]);

    return Array.from(keys).reduce((carry, key) => {
        const baseValue = source[key];
        const incomingValue = target[key];

        if (Array.isArray(incomingValue)) {
            carry[key] = incomingValue;
            return carry;
        }

        if (
            baseValue
            && incomingValue
            && typeof baseValue === 'object'
            && typeof incomingValue === 'object'
            && !Array.isArray(baseValue)
            && !Array.isArray(incomingValue)
        ) {
            carry[key] = deepMerge(baseValue, incomingValue);
            return carry;
        }

        carry[key] = incomingValue !== undefined ? incomingValue : baseValue;
        return carry;
    }, {});
}

function extractTextFromTipTap(value) {
    if (!value || typeof value !== 'object') {
        return '';
    }

    if (value.type === 'text') {
        return value.text || '';
    }

    if (!Array.isArray(value.content)) {
        return '';
    }

    return value.content
        .map((item) => extractTextFromTipTap(item))
        .filter(Boolean)
        .join(' ')
        .trim();
}

function normalizeLegacySettings(type, settings = {}) {
    const normalized = { ...settings };

    if (type === 'paragraph') {
        return {
            content: typeof normalized.content === 'object'
                ? extractTextFromTipTap(normalized.content)
                : normalized.content || normalized.text || '',
        };
    }

    if (type === 'form-embed') {
        return {
            form_id: normalized.form_id || normalized.formId || '',
            title: normalized.title || '',
            description: normalized.description || '',
        };
    }

    if (type === 'text' && typeof normalized.content === 'object') {
        normalized.content = extractTextFromTipTap(normalized.content);
    }

    if (type === 'heading' && typeof normalized.content === 'object' && !normalized.text) {
        normalized.text = extractTextFromTipTap(normalized.content);
    }

    if (type === 'container' && normalized.padding && typeof normalized.padding === 'object') {
        normalized.padding_top ??= normalized.padding.top ?? null;
        normalized.padding_bottom ??= normalized.padding.bottom ?? null;
        normalized.padding_left ??= normalized.padding.left ?? null;
        normalized.padding_right ??= normalized.padding.right ?? null;
    }

    return normalized;
}

function resolveComponent(type) {
    return experimentalRegistry[type]?.component ?? SchemaBlock;
}

function normalizeRemoteDefinition(block) {
    return {
        type: block.type,
        label: block.name,
        category: block.category,
        description: block.description,
        fields: block.fields || {},
        editor: block.editor || {},
        component: resolveComponent(block.type),
        defaultSettings: () => deepMerge({}, normalizeLegacySettings(
            block.type,
            block.default_block?.settings || {}
        )),
    };
}

function normalizeRemoteEntries(payload) {
    if (Array.isArray(payload?.entries)) {
        return payload.entries.map(normalizeRemoteDefinition);
    }

    if (Array.isArray(payload?.blocks)) {
        return payload.blocks.map(normalizeRemoteDefinition);
    }

    if (payload?.blocks && typeof payload.blocks === 'object') {
        return Object.values(payload.blocks).map(normalizeRemoteDefinition);
    }

    return [];
}

function experimentalEntries() {
    return Object.values(experimentalRegistry);
}

function mergedRegistryMap() {
    return {
        ...registryState.remoteMap,
        ...experimentalRegistry,
    };
}

export async function loadBuilderRegistry(endpoint = '/admin/api/builder/blocks') {
    if (registryState.loaded) {
        return registryState.remoteEntries;
    }

    if (registryState.loading) {
        return registryState.loading;
    }

    registryState.loading = fetch(endpoint, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    })
        .then(async (response) => {
            if (!response.ok) {
                throw new Error(`Builder registry request failed with status ${response.status}`);
            }

            const payload = await response.json();
            const entries = normalizeRemoteEntries(payload);

            registryState.remoteEntries = entries;
            registryState.remoteMap = Object.fromEntries(entries.map((entry) => [entry.type, entry]));
            registryState.loaded = true;

            return entries;
        })
        .finally(() => {
            registryState.loading = null;
        });

    return registryState.loading;
}

export function registryEntries() {
    return [
        ...registryState.remoteEntries,
        ...experimentalEntries().filter((entry) => !hiddenExperimentalTypes.has(entry.type) && !entry.paletteHidden),
    ];
}

export function blockDefinition(type) {
    return mergedRegistryMap()[type] ?? null;
}

export function createRegistryBlock(type) {
    const definition = blockDefinition(type);

    if (!definition) {
        throw new Error(`Unknown builder block type: ${type}`);
    }

    return {
        id: makeId(),
        type,
        settings: definition.defaultSettings(),
    };
}

export function normalizePrototypeBlocks(blocks = []) {
    return blocks
        .filter((block) => block && typeof block === 'object' && typeof block.type === 'string')
        .map((block) => {
            const legacyType = block.type;
            const resolvedType = legacyType === 'paragraph'
                ? 'text'
                : legacyType === 'form-embed'
                    ? 'form'
                    : legacyType;
            const definition = blockDefinition(resolvedType) ?? blockDefinition(legacyType);
            const defaults = definition ? definition.defaultSettings() : {};
            const settings = normalizeLegacySettings(resolvedType, block.settings || block.content || {});

            return {
                id: block.id || makeId(),
                type: resolvedType,
                settings: deepMerge(defaults, settings),
            };
        });
}
