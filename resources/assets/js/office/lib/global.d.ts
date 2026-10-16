export {};

declare global {
  interface Window {
    chimeInOffice: {
      reverbKey: string;
      reverbPort: string | number;
      authStartUrl: string;
    };
  }
}
