export class Estado {
    constructor(initial) {
        this.value = { ...initial };
        this.listeners = new Set();
    }
    subscribe(listener) {
        this.listeners.add(listener);
        listener(this.value);
        return () => this.listeners.delete(listener);
    }
    set(patch) {
        this.value = { ...this.value, ...patch };
        this.listeners.forEach((listener) => listener(this.value));
    }
}
