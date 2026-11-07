import { Network } from '@capacitor/network';
import { ref } from 'vue';

export const connected = ref(true);
export async function initializeNetwork(): Promise<void> {
  await Network.addListener('networkStatusChange', status => { connected.value = status.connected; });
  connected.value = (await Network.getStatus()).connected;
}
