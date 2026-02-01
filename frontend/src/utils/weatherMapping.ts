import {
    Sun, CloudSun, Cloud, CloudRain, CloudLightning, Snowflake, CloudDrizzle
} from 'lucide-vue-next';

export const getWeatherInfo = (code: number) => {
    // Codes basés sur la doc Open-Meteo (WMO Weather interpretation codes)
    if (code === 0) return { label: 'Ciel dégagé', icon: Sun, color: 'text-yellow-500' };
    if ([1, 2, 3].includes(code)) return { label: 'Nuageux', icon: CloudSun, color: 'text-gray-400' };
    if ([45, 48].includes(code)) return { label: 'Brouillard', icon: Cloud, color: 'text-slate-400' };
    if ([51, 53, 55, 56, 57].includes(code)) return { label: 'Bruine', icon: CloudDrizzle, color: 'text-blue-300' };
    if ([61, 63, 65, 66, 67, 80, 81, 82].includes(code)) return { label: 'Pluie', icon: CloudRain, color: 'text-blue-500' };
    if ([71, 73, 75, 77, 85, 86].includes(code)) return { label: 'Neige', icon: Snowflake, color: 'text-cyan-200' };
    if ([95, 96, 99].includes(code)) return { label: 'Orage', icon: CloudLightning, color: 'text-purple-500' };

    return { label: 'Inconnu', icon: Cloud, color: 'text-gray-500' };
};