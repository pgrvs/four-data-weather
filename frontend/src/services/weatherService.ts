import api from './api';
import type { WeatherData, SearchHistory } from '../types/Weather';

export const weatherService = {

    async searchCities(query: string) {
        const { data } = await api.get('/city/search', { params: { q: query } });
        return data.data;
    },

    async getWeather(lat: number, lon: number): Promise<WeatherData> {
        const { data } = await api.get('/weather', {
            params: {
                latitude: lat,
                longitude: lon
            }
        });
        return data.data;
    },

    async getHistory(): Promise<SearchHistory[]> {
        const { data } = await api.get('/searches');
        return data.data;
    },

    async saveSearch(city: string, lat: number, lon: number): Promise<SearchHistory> {
        const { data } = await api.post('/searches', {
            city: city,
            latitude: lat,
            longitude: lon
        });
        return data.data;
    },

    async deleteSearch(id: number): Promise<void> {
        await api.delete(`/searches/${id}`);
    }
};