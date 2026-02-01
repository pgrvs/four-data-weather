export interface WeatherUnits {
    temp: string;
    speed: string;
    rain: string;
}

export interface CurrentWeather {
    time: string;
    temperature: number;
    apparent_temperature: number;
    precipitation: number;
    probability: number;
    wind_speed: number;
    weathercode: number;
    is_day: number;
}

export interface HourlyForecast {
    time: string;
    full_time: string;
    temperature: number;
    apparent_temperature: number;
    precipitation: number;
    probability: number;
    wind_speed: number;
}

export interface DailyForecast {
    date: string;
    temperature_max: number;
    temperature_min: number;
    precipitation_sum: number;
    probability_max: number;
    weathercode: number;
}

export interface WeatherData {
    latitude: number;
    longitude: number;
    timezone: string;
    elevation: number;
    units: WeatherUnits;
    current: CurrentWeather;
    hourly: HourlyForecast[];
    daily: DailyForecast[];
    city?: string;
}

export interface SearchHistory {
    id: number;
    city: string;
    latitude: number;
    longitude: number;
    createdAt: string;
}