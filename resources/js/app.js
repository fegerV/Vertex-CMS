import './admin/app';

/**
 * Vertex CMS Core JavaScript
 * Handles Page Builder, Dashboards, and UI interactions
 */

document.addEventListener('alpine:init', () => {
    // --- Page Builder Logic ---
    Alpine.data('pageBuilder', () => ({
        blocks: [],
        selectedBlock: null,
        dragging: false,
        dragIndex: null,

        init() {
            // Load initial blocks from JSON input or default
            const jsonInput = document.getElementById('page-builder-data');
            if (jsonInput && jsonInput.value) {
                try {
                    this.blocks = JSON.parse(jsonInput.value);
                } catch (e) {
                    console.error('Invalid builder data', e);
                    this.blocks = [];
                }
            }
            
            // Setup drag and drop listeners
            this.setupDragAndDrop();
        },

        addBlock(type) {
            const newBlock = {
                id: 'block_' + Date.now(),
                type: type,
                content: {},
                settings: { class: '', style: '' }
            };
            
            // Default content based on type
            if (type === 'hero') newBlock.content = { title: 'Welcome', subtitle: 'Subtitle' };
            if (type === 'text') newBlock.content = { text: 'Lorem ipsum...' };
            if (type === 'image') newBlock.content = { src: '', alt: '' };
            
            this.blocks.push(newBlock);
            this.updateJson();
        },

        removeBlock(index) {
            if(confirm('Are you sure you want to remove this block?')) {
                this.blocks.splice(index, 1);
                this.selectedBlock = null;
                this.updateJson();
            }
        },

        selectBlock(index) {
            this.selectedBlock = index;
        },

        updateContent(key, value) {
            if (this.selectedBlock !== null) {
                this.blocks[this.selectedBlock].content[key] = value;
                this.updateJson();
            }
        },

        updateSettings(key, value) {
            if (this.selectedBlock !== null) {
                this.blocks[this.selectedBlock].settings[key] = value;
                this.updateJson();
            }
        },

        updateJson() {
            const jsonInput = document.getElementById('page-builder-data');
            if (jsonInput) {
                jsonInput.value = JSON.stringify(this.blocks);
                jsonInput.dispatchEvent(new Event('change'));
            }
        },

        setupDragAndDrop() {
            const container = document.getElementById('builder-canvas');
            if (!container) return;

            container.addEventListener('dragover', (e) => {
                e.preventDefault();
                this.dragging = true;
            });

            container.addEventListener('drop', (e) => {
                e.preventDefault();
                this.dragging = false;
            });
        },

        moveBlock(fromIndex, toIndex) {
            if (toIndex < 0 || toIndex >= this.blocks.length) return;
            const item = this.blocks.splice(fromIndex, 1)[0];
            this.blocks.splice(toIndex, 0, item);
            this.selectedBlock = toIndex;
            this.updateJson();
        }
    }));

    // --- Dashboard Charts Logic ---
    Alpine.data('dashboardStats', () => ({
        chartInstance: null,
        stats: { visitors: 0, sales: 0, revenue: 0 },

        init() {
            this.fetchStats();
            this.initChart();
        },

        async fetchStats() {
            try {
                const response = await fetch('/admin/api/dashboard/stats');
                if (response.ok) {
                    this.stats = await response.json();
                }
            } catch (e) {
                console.warn('Could not load dashboard stats', e);
            }
        },

        initChart() {
            const ctx = document.getElementById('revenueChart');
            if (!ctx || typeof Chart === 'undefined') return;

            this.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [120, 190, 300, 500, 200, 300, 450],
                        borderColor: '#4F46E5',
                        tension: 0.4,
                        fill: true,
                        backgroundColor: 'rgba(79, 70, 229, 0.1)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    }));

    // --- Confirmation Modal Logic ---
    Alpine.data('confirmAction', (message, callback) => ({
        open: false,
        message: message,
        confirm() {
            this.open = false;
            if (typeof callback === 'function') callback();
            else if (typeof callback === 'string') {
                document.getElementById(callback)?.submit();
                window.location.href = callback;
            }
        },
        cancel() {
            this.open = false;
        },
        show() {
            this.open = true;
        }
    }));
});

// Helper for global confirmations
window.vertexConfirm = function(message, callback) {
    if (confirm(message)) {
        callback();
    }
};

