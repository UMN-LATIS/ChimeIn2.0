<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ChimeIn</title>
    <script src="https://appsforoffice.microsoft.com/lib/1/hosted/office.js"></script>
    <style>
        body { font-family: system-ui, -apple-system, "Segoe UI", sans-serif; margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem; text-align: center; color: #333; }
    </style>
</head>
<body>
    <p id="status">Finishing sign in&hellip;</p>

    {{-- json_encode via @json keeps the token out of the HTML attribute surface. --}}
    <script>
        (function () {
            var payload = @json($payload);

            function fallback() {
                document.getElementById('status').textContent =
                    payload.status === 'ok'
                        ? 'Signed in. You can close this window.'
                        : payload.message;
            }

            if (typeof Office === 'undefined') {
                fallback();
                return;
            }

            Office.onReady(function () {
                try {
                    Office.context.ui.messageParent(JSON.stringify(payload));
                } catch (e) {
                    fallback();
                }
            });
        })();
    </script>
</body>
</html>
