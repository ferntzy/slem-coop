<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div
    x-data="{
        map: null,
        marker: null,

        init() {
            this.$nextTick(() => setTimeout(() => this.initMap(), 500));
        },

        initMap() {
            if (this.map) return;

            const lat = parseFloat(document.getElementById('input_maps_lat').value) || 14.5995;
            const lng = parseFloat(document.getElementById('input_maps_lng').value) || 120.9842;
            const hasPin = !!document.getElementById('input_maps_lat').value;

            this.map = L.map(this.$refs.canvas, { preferCanvas: true })
                .setView([lat, lng], hasPin ? 16 : 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(this.map);

            if (hasPin) this.drop(lat, lng);

            this.map.on('click', (e) => {
                this.drop(e.latlng.lat, e.latlng.lng);
                this.writeValues(e.latlng.lat, e.latlng.lng);
            });
        },

        drop(lat, lng) {
            if (this.marker) this.map.removeLayer(this.marker);
            this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
            this.marker.on('dragend', (e) => {
                const p = e.target.getLatLng();
                this.writeValues(p.lat, p.lng);
            });
        },

        writeValues(lat, lng) {
            const latS = parseFloat(lat).toFixed(7);
            const lngS = parseFloat(lng).toFixed(7);

            // Write directly into the hidden Livewire-bound inputs
            // This bypasses $wire.set() entirely so the map never re-renders
            const latInput   = document.getElementById('input_maps_lat');
            const lngInput   = document.getElementById('input_maps_lng');
            const embedInput = document.getElementById('input_maps_embed_url');

            if (latInput)   { latInput.value   = latS;              latInput.dispatchEvent(new Event('input')); }
            if (lngInput)   { lngInput.value   = lngS;              lngInput.dispatchEvent(new Event('input')); }
            if (embedInput) { embedInput.value = latS + ',' + lngS; embedInput.dispatchEvent(new Event('input')); }
        },

        async search() {
            const q = this.$refs.searchBox.value.trim();
            if (!q) return;
            try {
                const r    = await fetch(
                    'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q),
                    { headers: { 'Accept-Language': 'en' } }
                );
                const data = await r.json();
                if (!data.length) { alert('Address not found. Try a more specific search.'); return; }
                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);
                this.map.setView([lat, lng], 17);
                this.drop(lat, lng);
                this.writeValues(lat, lng);
            } catch { alert('Search failed. Check your connection.'); }
        },
    }"
    x-init="init()"
    class="w-full"
>
    {{-- Search --}}
    <div class="mb-3 flex gap-2">
        <input
            x-ref="searchBox"
            type="text"
            placeholder="Search address (e.g. 123 Rizal St, Cebu City)..."
            @keydown.enter.prevent="search()"
            class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
        />
        <button
            type="button"
            @click="search()"
            class="px-4 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium shadow-sm transition"
        >
            Search
        </button>
    </div>

    {{-- Map canvas --}}
    <div
        x-ref="canvas"
        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm"
        style="height: 420px; z-index: 0; position: relative;"
    ></div>

    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
        🖱 Click on the map to drop a pin &middot; Drag to fine-tune &middot; Use Search to find an address &middot; Powered by OpenStreetMap (free, no API key)
    </p>

    {{-- Hidden inputs wired directly to Livewire form state --}}
    {{-- Filament renders TextInput fields with wire:model — we target their actual DOM input IDs --}}
    {{-- The IDs below match Filament's generated IDs for statePath('data') fields --}}
    <input type="hidden" id="input_maps_lat"       wire:model="data.maps_lat"       />
    <input type="hidden" id="input_maps_lng"       wire:model="data.maps_lng"       />
    <input type="hidden" id="input_maps_embed_url" wire:model="data.maps_embed_url" />
</div>
