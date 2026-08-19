@extends("admin.layouts.app")
@section("title","Form Builder")
@section("page_title",$form?->name ? "Edit: $form->name" : "New Form")
@section("content")
<div class="h-screen flex" 
     x-data="formBuilder(
         {{ $form?->id ?? "null" }},
         {!! $form ? $form->load("fields")->toJson() : "{}" !!}
     )"
>
    <style>
        .field-btn { @apply w-full text-left px-3 py-2 border rounded hover:bg-blue-50 flex items-center gap-2; }
    </style>

    <!-- Field Palette -->
    <div class="w-60 bg-white border-r p-4 overflow-y-auto">
        <h3 class="font-semibold mb-3 text-gray-800">Fields</h3>
        <div class="space-y-1">
            <template x-for="t in fieldTypes" :key="t.code">
                <button @click="addField(t.code)" class="field-btn">
                    <span x-text="t.icon"></span>
                    <span x-text="t.label"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Canvas -->
    <div class="flex-1 bg-gray-50 p-6 overflow-y-auto">
        <div class="max-w-2xl mx-auto">
            <input type="text" x-model="name" placeholder="Form Name" class="w-full text-2xl font-bold border-none focus:ring-0 mb-1" style="outline:none">
            <textarea x-model="description" placeholder="Description" class="w-full border-none focus:ring-0 mb-6 resize-none text-gray-600" rows="1"></textarea>

            <div @drop="onDrop" @dragover.prevent @dragleave="dragOver=false"
                 class="min-h-[400px] border-2 border-dashed rounded-lg p-8 transition-all"
                 :class="dragOver ? \'border-blue-400 bg-blue-50\' : \'border-gray-300\'">
                <template x-for="(field,idx) in fields" :key="field._uid">
                    <div @click="selectField(idx)"
                         :class="selectedIndex===idx ? \'border-blue-300 ring-2 ring-blue-100\' : \'border-gray-200 hover:border-gray-300\'"
                         class="bg-white border rounded-lg p-4 mb-3 cursor-pointer">
                        <div class="flex justify-between">
                            <div>
                                <span class="font-medium" x-text="field.label"></span>
                                <span class="text-xs bg-gray-100 px-2 py-0.5 rounded ml-2" x-text="field.type"></span>
                                <span x-show="field.required" class="text-red-500 text-xs">*</span>
                            </div>
                            <div class="flex gap-2">
                                <button @click.stop="duplicate(idx)" class="text-gray-500 hover:text-blue-600">Copy</button>
                                <button @click.stop="remove(idx)" class="text-gray-500 hover:text-red-600">Delete</button>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="fields.length===0" class="text-center text-gray-400 py-20">Drop fields here</div>
            </div>
        </div>
    </div>

    <!-- Inspector -->
    <div class="w-80 bg-white border-l p-4 overflow-y-auto">
        <h3 class="font-semibold mb-4 text-gray-800">Properties</h3>
        <template x-if="selectedField !== null">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Label</label>
                    <input type="text" x-model="selectedField.label" @input="dirty=true" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Name</label>
                    <input type="text" x-model="selectedField.name" @input="dirty=true" class="w-full border rounded px-3 py-2 font-mono text-sm">
                </div>
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" x-model="selectedField.required" @change="dirty=true" class="rounded">
                        <span class="text-sm font-medium">Required</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Placeholder</label>
                    <input type="text" x-model="selectedField.placeholder" @input="dirty=true" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Help Text</label>
                    <textarea x-model="selectedField.help_text" @input="dirty=true" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                <!-- Calculator Options -->
                <template x-if="selectedField.type === `calculator`">
                    <div class="border-t pt-4 mt-4 space-y-3">
                        <h4 class="text-sm font-semibold text-gray-800">Calculator</h4>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Formula</label>
                            <input type="text" x-model="selectedField.options.formula" @input="dirty=true" class="w-full border rounded px-3 py-2 text-sm font-mono" placeholder="{qty} * {price}">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Depends On</label>
                            <input type="text" 
                                   :value="(selectedField.options.depends_on||[]).join(`, \')"
                                   @input="selectedField.options.depends_on = $event.target.value.split(',').map(s=>s.trim()).filter(Boolean); dirty=true"
                                   class="w-full border rounded px-3 py-2 text-sm" placeholder="width, height">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Prefix</label>
                                <input type="text" x-model="selectedField.options.prefix" @input="dirty=true" class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Suffix</label>
                                <input type="text" x-model="selectedField.options.suffix" @input="dirty=true" class="w-full border rounded px-3 py-2">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Precision</label>
                            <input type="number" x-model="selectedField.options.precision" @input="dirty=true" min="0" max="10" class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </template>
            </div>
        </template>
        <template x-if="selectedField === null">
            <p class="text-sm text-gray-500">Select a field to edit properties.</p>
        </template>
    </div>
</div>

<!-- Save FAB -->
<button @click="save" :disabled="saving" 
        class="fixed bottom-6 right-6 bg-blue-600 text-white px-6 py-3 rounded-full shadow-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
    <span x-text="saving ? \'Saving...\' : \'Save\'"></span>
</button>

@push("scripts")
<script>
document.addEventListener("alpine:init", () => {
    Alpine.data("formBuilder", (formId, initial) => ({
        name: initial.name || "",
        description: initial.description || "",
        settings: initial.settings || { submit_label: "Submit", success_message: "Thank you!" },
        fields: (initial.fields || []).map(f => ({...f, _uid: Math.random().toString(36).slice(2)})),
        selectedUid: null,
        dragOver: false,
        saving: false,
        dirty: false,

        fieldTypes: [
            {code:"text",label:"Text Input",icon:"??"},
            {code:"email",label:"Email",icon:"??"},
            {code:"tel",label:"Phone",icon:"??"},
            {code:"number",label:"Number",icon:"??"},
            {code:"textarea",label:"Textarea",icon:"??"},
            {code:"select",label:"Dropdown",icon:"?"},
            {code:"radio",label:"Radio",icon:"??"},
            {code:"checkbox",label:"Checkbox",icon:"??"},
            {code:"calculator",label:"Calculator",icon:"??"},
            {code:"file",label:"File Upload",icon:"??"},
            {code:"date",label:"Date",icon:"??"},
            {code:"heading",label:"Heading",icon:"??"},
            {code:"divider",label:"Divider",icon:"?"},
            {code:"html",label:"HTML",icon:"??"},
        ],

        get selectedIndex() { return this.fields.findIndex(f => f._uid === this.selectedUid); },
        get selectedField() { return this.fields[this.selectedIndex] || null; },

        addField(type) {
            const defs = {
                text:{label:"Text Field",placeholder:"",required:false},
                email:{label:"Email",placeholder:"",required:false},
                number:{label:"Number",placeholder:"",required:false},
                textarea:{label:"Text Area",placeholder:"",required:false},
                select:{label:"Dropdown",options:{choices:[]}},
                calculator:{label:"Calculator",options:{formula:"",depends_on:[],prefix:"",suffix:"",precision:2}},
                heading:{label:"Heading",level:"h2"},
                divider:{label:"Divider"},
                html:{label:"HTML"},
            };
            const def = defs[type] || {label: type.charAt(0).toUpperCase()+type.slice(1)};
            const field = {
                _uid: Date.now()+Math.random(),
                type, name: type+"_"+Date.now(), label: def.label,
                required: def.required||false, visible: true,
                placeholder: def.placeholder||"", help_text:"",
                options: def.options || {}, css_class:"",
            };
            this.fields.push(field);
            this.selectedUid = field._uid;
            this.dirty = true;
        },

        remove(idx) { this.fields.splice(idx,1); this.selectedUid = null; this.dirty = true; },
        duplicate(idx) {
            const copy = JSON.parse(JSON.stringify(this.fields[idx]));
            copy._uid = Date.now()+Math.random();
            copy.name = copy.name + "_copy";
            this.fields.splice(idx+1,0,copy);
            this.selectedUid = copy._uid;
            this.dirty = true;
        },
        selectField(idx) { this.selectedUid = this.fields[idx]._uid; },

        startDrag(e, type) { e.dataTransfer.setData("fieldType", type); },

        onDrop(e) {
            const type = e.dataTransfer.getData("fieldType");
            if (type) this.addField(type);
            this.dragOver = false;
        },

        async save() {
            this.saving = true;
            const payload = {
                name: this.name, description: this.description,
                type: "standard", settings: this.settings,
                fields: this.fields.map(({_uid, ...rest}) => rest),
            };
            try {
                const method = formId ? "PUT" : "POST";
                const url = formId ? `/admin/forms/${formId}` : `/admin/forms`;
                const res = await fetch(url, {
                    method, headers: {
                        "Content-Type":"application/json",
                        "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
                    },
                    body: JSON.stringify(payload),
                });
                if (!res.ok) {
                    const err = await res.json();
                    alert("Save failed: " + (err.message || JSON.stringify(err.errors)));
                } else {
                    alert("Saved!");
                    this.dirty = false;
                }
            } catch(e) { alert("Network error"); }
            finally { this.saving = false; }
        },

        preview() { window.open(`/admin/forms/${formId}/preview`, "_blank"); },
    }));
});
@endpush
@endsection
