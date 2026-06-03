<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Install VertexCMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style>
        [v-cloak] { display: none; }
        .step-active { color: #3b82f6; border-color: #3b82f6; }
        .step-complete { color: #10b981; border-color: #10b981; }
        .step-inactive { color: #94a3b8; border-color: #cbd5e1; }
    </style>
</head>
<body class="h-full">
    <div id="installer" v-cloak class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                VertexCMS Installation
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Step @{{ currentStep }} of @{{ totalSteps }}: @{{ stepTitle }}
            </p>
        </div>

        <!-- Progress Bar -->
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="flex items-center justify-between px-4 sm:px-0">
                <div v-for="s in totalSteps" :key="s" class="flex flex-col items-center flex-1">
                    <div :class="['w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold transition-colors', 
                                 s === currentStep ? 'step-active' : (s < currentStep ? 'step-complete' : 'step-inactive')]">
                        <svg v-if="s < currentStep" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span v-else>@{{ s }}</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 h-1 bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 transition-all duration-500" :style="{ width: ((currentStep - 1) / (totalSteps - 1) * 100) + '%' }"></div>
            </div>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-slate-200">
                
                <!-- Error Alert -->
                <div v-if="error" class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">@{{ error }}</p>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Requirements -->
                <div v-if="currentStep === 1" class="space-y-6">
                    <p class="text-sm text-gray-500">Checking your server configuration...</p>
                    <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                        <li v-for="(status, req) in requirements" :key="req" class="flex items-center justify-between px-4 py-3 text-sm">
                            <span class="font-medium text-gray-700 capitalize">@{{ req.replace(/_/g, ' ') }}</span>
                            <span v-if="status" class="text-green-600 flex items-center">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                OK
                            </span>
                            <span v-else class="text-red-600 flex items-center font-bold">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                FAILED
                            </span>
                        </li>
                    </ul>
                    <div class="flex justify-end">
                        <button 
                            @click="nextStep" 
                            :disabled="!requirementsOk"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            Next: Database
                        </button>
                    </div>
                </div>

                <!-- Step 2: Database -->
                <div v-if="currentStep === 2" class="space-y-6">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-4">
                            <label class="block text-sm font-medium text-gray-700">DB Host</label>
                            <input type="text" v-model="form.DB_HOST" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Port</label>
                            <input type="number" v-model="form.DB_PORT" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Database Name</label>
                            <input type="text" v-model="form.DB_DATABASE" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" v-model="form.DB_USERNAME" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" v-model="form.DB_PASSWORD" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <button @click="prevStep" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Back
                        </button>
                        <button 
                            @click="checkDatabase" 
                            :disabled="loading"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            @{{ loading ? 'Checking...' : 'Next: Site Config' }}
                        </button>
                    </div>
                </div>

                <!-- Step 3: Site Config -->
                <div v-if="currentStep === 3" class="space-y-6">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Site Name</label>
                            <input type="text" v-model="form.site_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Site URL</label>
                            <input type="url" v-model="form.site_url" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Locale</label>
                            <select v-model="form.site_locale" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="en">English</option>
                                <option value="ru">Russian</option>
                                <option value="de">German</option>
                                <option value="fr">French</option>
                            </select>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Timezone</label>
                            <select v-model="form.site_timezone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="UTC">UTC</option>
                                <option value="Europe/Moscow">Europe/Moscow</option>
                                <option value="America/New_York">America/New_York</option>
                                <option value="Europe/Berlin">Europe/Berlin</option>
                            </select>
                        </div>
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">System Email</label>
                            <input type="email" v-model="form.site_admin_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <button @click="prevStep" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Back
                        </button>
                        <button @click="nextStep" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Next: Admin Account
                        </button>
                    </div>
                </div>

                <!-- Step 4: Admin Account -->
                <div v-if="currentStep === 4" class="space-y-6">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" v-model="form.admin_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Admin Email</label>
                            <input type="email" v-model="form.admin_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" v-model="form.admin_password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" v-model="form.admin_password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <button @click="prevStep" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Back
                        </button>
                        <button 
                            @click="runInstallation" 
                            :disabled="loading"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                        >
                            @{{ loading ? 'Installing...' : 'Complete Installation' }}
                        </button>
                    </div>
                </div>

                <!-- Step 5: Finished -->
                <div v-if="currentStep === 5" class="text-center space-y-6">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Installation Successful!</h3>
                    <p class="text-sm text-gray-500">
                        VertexCMS has been installed. You can now log in to the administration panel.
                    </p>
                    <div class="mt-5">
                        <a href="/admin/login" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Go to Admin Login
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const { createApp, ref, computed, onMounted } = Vue;

        createApp({
            setup() {
                const currentStep = ref(1);
                const totalSteps = 5;
                const loading = ref(false);
                const error = ref(null);
                const requirements = ref(@json($requirements));

                const form = ref({
                    DB_HOST: '127.0.0.1',
                    DB_PORT: 3306,
                    DB_DATABASE: 'vertex_cms',
                    DB_USERNAME: 'root',
                    DB_PASSWORD: '',
                    site_name: 'Vertex CMS',
                    site_url: window.location.origin,
                    site_locale: 'en',
                    site_timezone: 'UTC',
                    site_admin_email: 'admin@example.com',
                    admin_name: 'Admin',
                    admin_email: 'admin@example.com',
                    admin_password: '',
                    admin_password_confirmation: ''
                });

                const stepTitle = computed(() => {
                    const titles = [
                        'Server Requirements',
                        'Database Connection',
                        'Site Configuration',
                        'Admin Account',
                        'Installation Complete'
                    ];
                    return titles[currentStep.value - 1];
                });

                const requirementsOk = computed(() => {
                    return !Object.values(requirements.value).includes(false);
                });

                const nextStep = () => {
                    if (currentStep.value < totalSteps) {
                        currentStep.value++;
                        error.value = null;
                    }
                };

                const prevStep = () => {
                    if (currentStep.value > 1) {
                        currentStep.value--;
                        error.value = null;
                    }
                };

                const checkDatabase = async () => {
                    loading.value = true;
                    error.value = null;
                    try {
                        const response = await fetch('/install/check-database', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                DB_HOST: form.value.DB_HOST,
                                DB_PORT: form.value.DB_PORT,
                                DB_DATABASE: form.value.DB_DATABASE,
                                DB_USERNAME: form.value.DB_USERNAME,
                                DB_PASSWORD: form.value.DB_PASSWORD
                            })
                        });
                        const data = await response.json();
                        if (response.ok && data.ok) {
                            nextStep();
                        } else {
                            if (data.errors) {
                                error.value = Object.values(data.errors).flat().join(' ');
                            } else {
                                error.value = data.message || 'Database connection failed';
                            }
                        }
                    } catch (e) {
                        error.value = 'Failed to connect to the server';
                    } finally {
                        loading.value = false;
                    }
                };

                const runInstallation = async () => {
                    loading.value = true;
                    error.value = null;
                    try {
                        // First save config
                        await fetch('/install/save-config', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(form.value)
                        });

                        // Then run installation
                        const response = await fetch('/install/run', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(form.value)
                        });
                        const data = await response.json();
                        if (response.ok && data.ok) {
                            nextStep();
                        } else {
                            if (data.errors) {
                                error.value = Object.values(data.errors).flat().join(' ');
                            } else {
                                error.value = data.message || data.error || 'Installation failed';
                            }
                        }
                    } catch (e) {
                        error.value = 'An error occurred during installation';
                    } finally {
                        loading.value = false;
                    }
                };

                return {
                    currentStep, totalSteps, stepTitle, loading, error, requirements, form,
                    requirementsOk, nextStep, prevStep, checkDatabase, runInstallation
                };
            }
        }).mount('#installer');
    </script>
</body>
</html>
