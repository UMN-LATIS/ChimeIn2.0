import { clearMachine, readMachine, writeMachine } from "./storage";

const BROWSE_TOKEN_KEY = "chimein:browseToken";
const LAST_CHIME_KEY = "chimein:lastChimeId";

interface StoredBrowseToken {
  token: string;
  expiresAt: string | null;
  user: { id: number; name: string; email: string };
}

interface AuthMessage {
  status: "ok" | "error";
  message?: string;
  token?: string;
  expires_at?: string | null;
  user?: StoredBrowseToken["user"];
}

export function getStoredBrowseToken(): StoredBrowseToken | null {
  const stored = readMachine<StoredBrowseToken>(BROWSE_TOKEN_KEY);
  if (!stored?.token) return null;

  if (stored.expiresAt && new Date(stored.expiresAt) <= new Date()) {
    clearMachine(BROWSE_TOKEN_KEY);
    return null;
  }

  return stored;
}

export function forgetBrowseToken(): void {
  clearMachine(BROWSE_TOKEN_KEY);
}

export function getLastChimeId(): number | null {
  return readMachine<number>(LAST_CHIME_KEY);
}

export function rememberLastChimeId(chimeId: number): void {
  writeMachine(LAST_CHIME_KEY, chimeId);
}

/**
 * Sign in through an Office dialog. window.open is unreliable inside add-in
 * webviews, so the dialog API is the only supported route.
 */
export function signIn(): Promise<StoredBrowseToken> {
  return new Promise((resolve, reject) => {
    const url = new URL(
      window.chimeInOffice.authStartUrl,
      window.location.origin
    ).toString();

    Office.context.ui.displayDialogAsync(
      url,
      { height: 60, width: 40, promptBeforeOpen: false },
      (asyncResult) => {
        if (asyncResult.status !== Office.AsyncResultStatus.Succeeded) {
          reject(new Error(asyncResult.error.message));
          return;
        }

        const dialog = asyncResult.value;

        dialog.addEventHandler(
          Office.EventType.DialogMessageReceived,
          (arg: { message: string; origin?: string } | { error: number }) => {
            dialog.close();

            if (!("message" in arg)) {
              reject(new Error("Sign in failed."));
              return;
            }

            let payload: AuthMessage;
            try {
              payload = JSON.parse(arg.message);
            } catch {
              reject(new Error("Sign in returned an unexpected response."));
              return;
            }

            if (payload.status !== "ok" || !payload.token || !payload.user) {
              reject(new Error(payload.message ?? "Sign in failed."));
              return;
            }

            const stored: StoredBrowseToken = {
              token: payload.token,
              expiresAt: payload.expires_at ?? null,
              user: payload.user,
            };

            writeMachine(BROWSE_TOKEN_KEY, stored);
            resolve(stored);
          }
        );

        dialog.addEventHandler(Office.EventType.DialogEventReceived, () => {
          reject(new Error("Sign in was cancelled."));
        });
      }
    );
  });
}
