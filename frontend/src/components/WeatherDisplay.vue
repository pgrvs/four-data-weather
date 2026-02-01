<script setup lang="ts">
import { MapPin, Wind, Star, Thermometer, CloudRain, Umbrella } from 'lucide-vue-next';
import { getWeatherInfo } from '../utils/weatherMapping';
import type { WeatherData } from '../types/Weather';

defineProps<{
  weather: WeatherData;
  cityName: string;
  isFavorite: boolean;
}>();

const emit = defineEmits(['toggle']);
</script>

<template>
  <div class="space-y-8 animate-fade-in pb-10">

    <div class="bg-gradient-to-br from-sky-600 to-indigo-900 rounded-3xl p-6 shadow-2xl text-white relative overflow-hidden border border-white/10">

      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
          <h2 class="text-3xl font-bold flex items-center gap-2">
            <MapPin class="w-6 h-6 text-sky-300" /> {{ cityName }}
          </h2>
          <p class="text-sky-200 text-sm mt-1">
            {{ weather.latitude }}, {{ weather.longitude }}
          </p>
        </div>
        <button
            @click="emit('toggle')"
            class="py-1.5 px-4 cursor-pointer rounded-full transition backdrop-blur-sm flex items-center gap-2 font-semibold border text-sm group"
            :class="isFavorite
            ? 'bg-yellow-500/20 border-yellow-400 text-yellow-300 hover:bg-yellow-500/30'
            : 'bg-white/10 border-white/20 text-white hover:bg-white/20'"
        >
          <Star class="w-4 h-4 transition-colors" :class="{'fill-current text-yellow-400': isFavorite}" />

          <span>{{ isFavorite ? 'Enregistré' : 'Sauvegarder' }}</span>
        </button>
      </div>

      <div class="flex flex-col lg:flex-row items-center justify-between gap-8">

        <div class="flex items-center gap-6">
          <component :is="getWeatherInfo(weather.current.weathercode).icon" class="w-20 h-20 text-sky-200 drop-shadow-lg" />
          <div>
            <div class="text-7xl font-black tracking-tighter drop-shadow-xl">
              {{ Math.round(weather.current.temperature) }}°
            </div>
            <div class="text-lg font-medium text-sky-200">
              {{ getWeatherInfo(weather.current.weathercode).label }}
            </div>
          </div>
        </div>

        <div class="w-full lg:w-auto grid grid-cols-2 md:grid-cols-4 gap-3">

          <div class="bg-black/20 p-3 rounded-xl backdrop-blur-md border border-white/10 flex flex-col items-center min-w-[100px]">
            <Wind class="w-5 h-5 mb-1 text-sky-300" />
            <span class="text-lg font-bold">{{ weather.current.wind_speed }}</span>
            <span class="text-[10px] text-sky-200 uppercase">km/h</span>
          </div>

          <div class="bg-black/20 p-3 rounded-xl backdrop-blur-md border border-white/10 flex flex-col items-center min-w-[100px]">
            <CloudRain class="w-5 h-5 mb-1 text-cyan-300" />
            <span class="text-lg font-bold">{{ weather.current.precipitation }}</span>
            <span class="text-[10px] text-sky-200 uppercase">mm</span>
          </div>

          <div class="bg-black/20 p-3 rounded-xl backdrop-blur-md border border-white/10 flex flex-col items-center min-w-[100px]">
            <Umbrella class="w-5 h-5 mb-1 text-blue-300" />
            <span class="text-lg font-bold">{{ weather.current.probability }}%</span>
            <span class="text-[10px] text-sky-200 uppercase">Proba</span>
          </div>

          <div class="bg-black/20 p-3 rounded-xl backdrop-blur-md border border-white/10 flex flex-col items-center min-w-[100px]">
            <Thermometer class="w-5 h-5 mb-1 text-yellow-300" />
            <span class="text-lg font-bold">{{ Math.round(weather.current.apparent_temperature) }}°</span>
            <span class="text-[10px] text-sky-200 uppercase">Ressenti</span>
          </div>

        </div>
      </div>
    </div>

    <div>
      <h3 class="text-xl font-bold text-slate-200 mb-4 pl-1">7 Prochains jours</h3>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        <div
            v-for="(day, index) in weather.daily"
            :key="index"
            class="bg-slate-800 p-6 rounded-2xl border border-slate-700 hover:border-sky-500 hover:bg-slate-750 transition-all duration-300 group shadow-lg flex flex-col justify-between h-full min-h-[200px]"
        >
          <div class="flex justify-between items-start">
            <div>
                <span class="text-slate-100 font-bold capitalize text-xl block">
                  {{ new Date(day.date).toLocaleDateString('fr-FR', { weekday: 'long' }) }}
                </span>
              <span class="text-slate-400 text-sm">
                  {{ new Date(day.date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long' }) }}
                </span>
            </div>
            <component :is="getWeatherInfo(day.weathercode).icon" :class="`w-12 h-12 ${getWeatherInfo(day.weathercode).color}`" />
          </div>

          <div class="mt-4">
            <div class="flex justify-between text-sm mb-1 font-semibold">
              <span class="text-sky-300">Min {{ Math.round(day.temperature_min) }}°</span>
              <span class="text-white">Max {{ Math.round(day.temperature_max) }}°</span>
            </div>
            <div class="w-full bg-slate-700 h-2 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-sky-600 via-sky-400 to-orange-400 w-full opacity-80"></div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 mt-6 pt-4 border-t border-slate-700/50">
            <div class="flex items-center gap-2 text-blue-300 bg-slate-900/50 p-2 rounded-lg justify-center">
              <Umbrella class="w-4 h-4" />
              <span class="font-bold">{{ day.probability_max }}%</span>
            </div>
            <div class="flex items-center gap-2 text-cyan-300 bg-slate-900/50 p-2 rounded-lg justify-center">
              <CloudRain class="w-4 h-4" />
              <span class="font-bold">{{ day.precipitation_sum }}mm</span>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>