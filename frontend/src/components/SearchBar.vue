<script setup lang="ts">
import { ref } from 'vue';
import { Search, Loader2, Navigation, LocateFixed } from 'lucide-vue-next';
import { weatherService } from '../services/weatherService';

const mode = ref<'city' | 'coords'>('city');
const query = ref('');
const lat = ref<number | null>(null);
const lon = ref<number | null>(null);

const suggestions = ref<any[]>([]);
const loading = ref(false);
const showSuggestions = ref(false);

const emit = defineEmits(['city-selected']);

let timeout: ReturnType<typeof setTimeout>;

// 1. Gestion de l'autocomplétion (Ville)
const onInput = () => {
  if (query.value.length < 2) {
    suggestions.value = [];
    showSuggestions.value = false;
    return;
  }

  clearTimeout(timeout);
  loading.value = true;

  timeout = setTimeout(async () => {
    try {
      const results = await weatherService.searchCities(query.value);
      suggestions.value = results || [];
      showSuggestions.value = true;
    } catch (e) {
      console.error("Erreur API:", e);
    } finally {
      loading.value = false;
    }
  }, 300);
};

// 2. Sélection depuis la liste (Ville)
const selectCity = (city: any) => {
  query.value = city.name;
  showSuggestions.value = false;
  emit('city-selected', city); // On envoie l'objet { name, latitude, longitude }
};

// 3. Validation manuelle (Coordonnées)
const submitCoords = () => {
  if (lat.value && lon.value) {
    emit('city-selected', {
      name: `Position (${lat.value}, ${lon.value})`,
      latitude: lat.value,
      longitude: lon.value
    });
  }
};
</script>

<template>
  <div class="w-full relative">

    <div class="flex bg-slate-900 rounded-lg p-1 mb-3 border border-slate-700">
      <button
          @click="mode = 'city'"
          :class="{'bg-sky-600 text-white': mode === 'city', 'text-slate-400 hover:text-white': mode !== 'city'}"
          class="flex-1 py-1.5 text-xs font-medium cursor-pointer rounded-md transition flex justify-center items-center gap-2"
      >
        <Navigation class="w-3 h-3"/> Ville
      </button>
      <button
          @click="mode = 'coords'"
          :class="{'bg-sky-600 text-white': mode === 'coords', 'text-slate-400 hover:text-white': mode !== 'coords'}"
          class="flex-1 py-1.5 text-xs font-medium cursor-pointer rounded-md transition flex justify-center items-center gap-2"
      >
        <LocateFixed class="w-3 h-3"/> Coordonnées
      </button>
    </div>

    <div v-if="mode === 'city'" class="relative">
      <input
          v-model="query"
          @input="onInput"
          type="text"
          placeholder="Chercher une ville (ex: Paris)..."
          class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-10 pr-4 py-3 focus:ring-2 focus:ring-sky-500 outline-none text-white placeholder-slate-500"
      />
      <div class="absolute left-3 top-3.5 text-slate-400">
        <Loader2 v-if="loading" class="w-5 h-5 animate-spin" />
        <Search v-else class="w-5 h-5" />
      </div>

      <ul v-if="showSuggestions && suggestions.length > 0" class="absolute z-50 left-0 right-0 top-full mt-2 bg-slate-800 border border-slate-600 rounded-lg shadow-xl max-h-60 overflow-y-auto">
        <li
            v-for="(s, index) in suggestions"
            :key="index"
            @click="selectCity(s)"
            class="px-4 py-3 hover:bg-sky-600 cursor-pointer flex items-center justify-between border-b border-slate-700 last:border-0 text-white transition-colors"
        >
          <span class="flex flex-col">
            <span class="font-bold text-sm">{{ s.name }}</span>
            <span class="text-xs text-slate-400">{{ s.country }}</span>
          </span>
          <span class="text-xs text-slate-500 bg-slate-900 px-2 py-1 rounded">
             {{ Number(s.latitude).toFixed(2) }}, {{ Number(s.longitude).toFixed(2) }}
          </span>
        </li>
      </ul>
    </div>

    <div v-else class="space-y-3">
      <div class="flex gap-2">
        <div class="relative w-full">
          <input v-model.number="lat" type="number" step="0.01" placeholder="Latitude" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-3 outline-none focus:ring-2 focus:ring-sky-500 text-white text-sm" />
        </div>
        <div class="relative w-full">
          <input v-model.number="lon" type="number" step="0.01" placeholder="Longitude" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-3 outline-none focus:ring-2 focus:ring-sky-500 text-white text-sm" />
        </div>
      </div>
      <button
          @click="submitCoords"
          :disabled="!lat || !lon"
          class="w-full cursor-pointer bg-sky-600 hover:bg-sky-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-2 rounded-lg transition text-sm flex justify-center items-center gap-2"
      >
        <Search class="w-4 h-4" /> Valider
      </button>
    </div>

  </div>
</template>