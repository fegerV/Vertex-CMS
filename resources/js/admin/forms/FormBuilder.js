/**
 * FormBuilder - Vue 3 app for visual form construction
 * Architecture: Schema-based, iframe preview (future), Pinia-ready
 */
const { createApp, ref, computed, onMounted } = Vue;

export default function FormBuilder(options) {
    return createApp({
        setup() {
            // State
            const formId = ref(options.formId || null);
            const formName = ref(options.initialData?.name || "");
            const formDescription = ref(options.initialData?.description || "");
            const formSettings = ref(options.initialData?.settings || { submit_label: "Submit", success_message: "Thank you!" });
            const fields = ref((options.initialData?.fields || []).map(f => ({...f})));
            const selectedFieldId = ref(null);
            const saving = ref(false);
            const csrfToken = options.csrfToken;

            // Field type palette (could be fetched from /admin/api/forms/field-types)
            const fieldTypes = [
                { type: "text", label: "Text Input", icon: "??", category: "basic" },
                { type: "email", label: "Email", icon: "??", category: "basic" },
                { type: "tel", label: "Phone", icon: "??", category: "basic" },
                { type: "number", label: "Number", icon: "??", category: "basic" },
                { type: "textarea", label: "Textarea", icon: "??", category: "basic" },
                { type: "select", label: "Dropdown", icon: "?", category: "choice" },
                { type: "radio", label: "Radio Group", icon: "??", category: "choice" },
                { type: "checkbox", label: "Checkbox", icon: "??", category: "choice" },
                { type: "calculator", label: "Calculator", icon: "??", category: "advanced" },
                { type: "file", label: "File Upload", icon: "??", category: "advanced" },
                { type: "date", label: "Date", icon: "??", category: "basic" },
                { type: "heading", label: "Heading", icon: "??", category: "layout" },
                { type: "divider", label: "Divider", icon: "?", category: "layout" },
                { type: "html", label: "HTML", icon: "??", category: "advanced" },
            ];

            const selectedField = computed(() => fields.value.find(f => f.id === selectedFieldId.value));
            const selectedIndex = computed(() => fields.value.findIndex(f => f.id === selectedFieldId.value));

            // Generate unique ID
            const genId = () => "fld_" + Math.random().toString(36).substr(2, 9);

            // Add field
            function addField(type) {
                const defaults = {
                    text: { label: "Text Field", placeholder: "", required: false },
                    email: { label: "Email", placeholder: "", required: false },
                    number: { label: "Number", placeholder: "", required: false, min: null, max: null },
                    textarea: { label: "Text Area", placeholder: "", required: false, rows: 4 },
                    select: { label: "Dropdown", placeholder: "", required: false, options: { choices: [] } },
                    radio: { label: "Radio Group", required: false, options: { choices: [] } },
                    checkbox: { label: "Checkbox", required: false },
                    calculator: { label: "Calculator", required: false, options: { formula: "", depends_on: [], prefix: "", suffix: "", precision: 2 } },
                    file: { label: "File Upload", required: false, options: { max_size: 10240, mime_types: [], multiple: false } },
                    heading: { label: "Section Heading", level: "h2" },
                    divider: { label: "Divider" },
                    html: { label: "HTML", options: { content: "" } },
                };
                const def = defaults[type] || { label: type };
                const field = {
                    id: genId(),
                    type,
                    name: type + "_" + Math.floor(Math.random() * 1000),
                    label: def.label,
                    required: def.required || false,
                    visible: true,
                    placeholder: def.placeholder || "",
                    help_text: "",
                    options: def.options || {},
                    css_class: "",
                };
                fields.value.push(field);
                selectedFieldId.value = field.id;
            }

            // Remove field
            function removeField(index) {
                fields.value.splice(index, 1);
                selectedFieldId.value = null;
            }

            // Duplicate field
            function duplicateField(index) {
                const copy = JSON.parse(JSON.stringify(fields.value[index]));
                copy.id = genId();
                copy.name = copy.name + "_copy";
                fields.value.splice(index + 1, 0, copy);
                selectedFieldId.value = copy.id;
            }

            // Save form via API
            async function save() {
                saving.value = true;
                const payload = {
                    name: formName.value,
                    description: formDescription.value,
                    type: "standard",
                    settings: formSettings.value,
                    fields: fields.value,
                };

                try {
                    const method = formId.value ? "PUT" : "POST";
                    const url = formId.value ? `/admin/forms/${formId.value}` : `/admin/forms`;
                    const res = await fetch(url, {
                        method,
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });
                    if (!res.ok) {
                        const err = await res.json();
                        alert("Save failed: " + (err.message || JSON.stringify(err.errors || err)));
                    } else {
                        const data = await res.json();
                        if (!formId.value) {
                            // new form created, could redirect or update formId
                            console.log("Created form", data.form);
                        }
                        alert("Form saved successfully!");
                    }
                } catch (e) {
                    console.error(e);
                    alert("Network error");
                } finally {
                    saving.value = false;
                }
            }

            // Drag & drop support in canvas
            function onDragOver(e) {
                e.preventDefault();
                e.currentTarget.classList.add("border-blue-400", "bg-blue-50");
            }

            function onDragLeave(e) {
                e.currentTarget.classList.remove("border-blue-400", "bg-blue-50");
            }

            function onDrop(e) {
                e.preventDefault();
                e.currentTarget.classList.remove("border-blue-400", "bg-blue-50");
                const type = e.dataTransfer.getData("fieldType");
                if (type) addField(type);
            }

            onMounted(() => {
                // Setup drag drop on canvas
                const canvas = document.getElementById("canvas");
                if (canvas) {
                    canvas.addEventListener("dragover", onDragOver);
                    canvas.addEventListener("dragleave", onDragLeave);
                    canvas.addEventListener("drop", onDrop);

                    // Setup field palette drag
                    document.querySelectorAll(".field-palette-item").forEach(el => {
                        el.setAttribute("draggable", "true");
                        el.addEventListener("dragstart", (e) => {
                            e.dataTransfer.setData("fieldType", el.dataset.fieldType);
                        });
                    });
                }
            });

            return {
                formId,
                formName,
                formDescription,
                fields,
                selectedFieldId,
                selectedField,
                selectedIndex,
                fieldTypes,
                saving,
                addField,
                removeField,
                duplicateField,
                save,
            };
        },
    }).mount(options.el);
}
