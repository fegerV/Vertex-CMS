import { createApp, ref, reactive, computed, onMounted } from 'vue';

// Expose Vue composition API globally for inline scripts (e.g., page builder)
window.Vue = { createApp, ref, reactive, computed, onMounted };

// Admin UI utilities (theme toggle, sidebar)
import './admin/app';


