<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { weatherService } from './services/weatherService';
import type { WeatherData, SearchHistory } from './types/Weather';
import { Star, Trash2, Loader2, MapPin } from 'lucide-vue-next';

import SearchBar from './components/SearchBar.vue';
import WeatherDisplay from './components/WeatherDisplay.vue';

const weather = ref<WeatherData | null>(null);
const currentCityName = ref('');
const history = ref<SearchHistory[]>([]);
const loading = ref(false);
const error = ref('');

const handleCitySelected = async (cityData: any) => {
  loading.value = true;
  error.value = '';
  currentCityName.value = cityData.name;

  try {
    const data = await weatherService.getWeather(cityData.latitude, cityData.longitude);
    weather.value = data;

    if (currentCityName.value.startsWith('Position (')) {

      if (data.city && data.city !== 'Position personnalisée') {
        currentCityName.value = data.city;
      }

      else if (data.timezone) {
        const tzName = data.timezone.split('/').pop()?.replace(/_/g, ' ');
        if (tzName) currentCityName.value = tzName;
      }
    }

  } catch (e) {
    console.error(e);
    error.value = "Impossible de charger la météo.";
  } finally {
    loading.value = false;
  }
};

const loadHistory = async () => {
  history.value = await weatherService.getHistory();
};

const isCurrentFavorite = computed(() => {
  if (!currentCityName.value) return false;
  return history.value.some(h => h.city.toLowerCase() === currentCityName.value.toLowerCase());
});

const toggleFavorite = async () => {
  if (!weather.value || !currentCityName.value) return;

  if (isCurrentFavorite.value) {
    const favItem = history.value.find(h => h.city.toLowerCase() === currentCityName.value.toLowerCase());
    if (favItem) {
      await deleteFavorite(favItem.id);
    }
  }

  else {
    try {
      await weatherService.saveSearch(currentCityName.value, weather.value.latitude, weather.value.longitude);
      await loadHistory();
    } catch (e) {
      alert("Erreur lors de la sauvegarde.");
    }
  }
};

const loadFavorite = (item: SearchHistory) => {
  handleCitySelected({
    name: item.city,
    latitude: item.latitude,
    longitude: item.longitude
  });
};

const deleteFavorite = async (id: number) => {
  if(!confirm('Supprimer ?')) return;
  await weatherService.deleteSearch(id);
  history.value = history.value.filter(h => h.id !== id);
};

onMounted(loadHistory);
</script>

<template>
  <div class="h-full min-h-screen bg-slate-900 text-slate-100 font-sans p-6 overflow-y-auto">
    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-8">

      <div class="lg:col-span-1 space-y-6">
        <div class="bg-slate-800 p-5 rounded-xl shadow-lg border border-slate-700">
          <h2 class="text-sm font-semibold text-slate-400 mb-3 uppercase tracking-wider">Recherche</h2>
          <SearchBar @city-selected="handleCitySelected" />
        </div>

        <div class="bg-slate-800 p-5 rounded-xl shadow-lg border border-slate-700">
          <h3 class="text-sky-400 font-semibold mb-4 flex items-center gap-2">
            <Star class="w-4 h-4 fill-current" /> Favoris
          </h3>
          <ul class="space-y-2">
            <li v-for="item in history" :key="item.id" @click="loadFavorite(item)" class="flex justify-between items-center p-2 hover:bg-slate-700 rounded cursor-pointer group">
              <span class="font-medium text-sm">{{ item.city }}</span>
              <button @click.stop="deleteFavorite(item.id)" class="text-slate-500 hover:text-red-400 opacity-0 group-hover:opacity-100">
                <Trash2 class="w-4 h-4" />
              </button>
            </li>
          </ul>
        </div>
      </div>

      <div class="lg:col-span-3">

        <div v-if="loading" class="flex justify-center items-center h-96">
          <Loader2 class="w-16 h-16 text-sky-500 animate-spin" />
        </div>

        <div v-else-if="error" class="bg-red-500/10 text-red-200 p-4 rounded-xl text-center border border-red-500/30">
          {{ error }}
        </div>

        <WeatherDisplay
            v-else-if="weather"
            :weather="weather"
            :cityName="currentCityName"
            :isFavorite="isCurrentFavorite"
            @toggle="toggleFavorite"
        />

        <div v-else class="flex flex-col items-center justify-center h-full min-h-[400px] text-slate-500 border-2 border-dashed border-slate-800 rounded-3xl bg-slate-800/30">
          <MapPin class="w-12 h-12 mb-4 opacity-50" />
          <p class="text-lg">Sélectionnez une ville pour voir la météo.</p>
        </div>

      </div>
    </div>
  </div>
</template>