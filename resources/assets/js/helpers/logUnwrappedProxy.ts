// src/utils/logUnwrapped.ts
import { toRaw, isProxy } from "vue";

// helper debug function to unwrap any reactive proxies
// when logging objects to the console
function unwrapProxy(obj: any): any {
  if (isProxy(obj)) {
    obj = toRaw(obj);
  }

  if (Array.isArray(obj)) {
    return obj.map((item) => unwrapProxy(item));
  }

  if (obj !== null && typeof obj === "object") {
    const unwrappedObj: any = {};
    for (const key of Object.keys(obj)) {
      unwrappedObj[key] = unwrapProxy(obj[key]);
    }
    return unwrappedObj;
  }
  return obj;
}

export function logUnwrappedProxy(...args: any[]) {
  console.log(...args.map((arg) => unwrapProxy(arg)));
}
