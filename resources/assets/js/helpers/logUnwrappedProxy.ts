import { toRaw, isProxy } from "vue";

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

/**
 * Logs the unwrapped version of the provided arguments.
 * useful for debugging reactive objects.
 */
export function logUnwrappedProxy(...args: any[]) {
  console.log(...args.map((arg) => unwrapProxy(arg)));
}
