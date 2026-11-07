import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'cr.ac.gemdy.calendar',
  appName: 'Gemdy Calendar',
  webDir: 'dist',
  // Permite la API HTTP de la red local utilizada en el laboratorio.
  server: { androidScheme: 'https', cleartext: true },
  android: { allowMixedContent: true },
};

export default config;
