import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'cr.ac.gemdy.calendar',
  appName: 'Gemdy Calendar',
  webDir: 'dist',
  server: { androidScheme: 'https', cleartext: true },
  android: { allowMixedContent: true },
};

export default config;
