<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Minimal emulation of the handful of SharePoint REST endpoints PowerPoint hits when it
 * reads a trusted "app catalog" share. Windows can't sideload a manifest without either a
 * registry edit or a catalog URL, so we pretend to be a SharePoint site with a single
 * "Apps for Office" library holding our one manifest.
 */
class OfficeCatalogController extends Controller {
    /** Feature id of the "Apps for Office" library; Office filters the list collection on it. */
    private const APPS_FOR_OFFICE_FEATURE_ID = '61e874cd-3ac3-4531-8628-28c3acb78279';

    private const LIBRARY_NAME = 'AppsForOffice';

    private const MANIFEST_FILE = 'chimein.xml';

    public function handle(Request $request, $path = '') {
        $path = trim((string) $path, '/');
        $route = strtolower($path);
        $base = $this->basePath($request, $path);

        Log::debug('office.catalog request', [
            'method' => $request->method(),
            'path' => $request->path(),
            'query' => $request->getQueryString(),
            'accept' => $request->header('Accept'),
            'agent' => $request->header('User-Agent'),
        ]);

        // Office probes this to decide it is talking to SharePoint before it touches _api.
        if (Str::startsWith($route, '_vti_bin/')) {
            return $this->decorate(response('', 200));
        }

        if (Str::startsWith($route, '_api/contextinfo')) {
            return $this->odata($request, $this->contextInfo($base), false);
        }

        if (preg_match('#^_api/web/lists\(guid\'[^\']+\'\)/(items|rootfolder/files)#', $route, $m)) {
            return $m[1] === 'items'
                ? $this->odata($request, [$this->item($base)], true)
                : $this->odata($request, [$this->file($base)], true);
        }

        if (preg_match('#^_api/web/lists\(guid\'[^\']+\'\)/?$#', $route)) {
            return $this->odata($request, $this->list($base), false);
        }

        if (Str::startsWith($route, '_api/web/lists')) {
            return $this->odata($request, [$this->list($base)], true);
        }

        if (preg_match('#^_api/web/get(file|folder)by(server)?relative(url|path)#', $route)) {
            if (Str::contains($route, 'files')) {
                return $this->odata($request, [$this->file($base)], true);
            }

            return Str::endsWith($route, '$value')
                ? $this->manifestResponse()
                : $this->odata($request, $this->file($base), false);
        }

        if (Str::startsWith($route, '_api/web/currentuser')) {
            return $this->odata($request, $this->currentUser($base), false);
        }

        if ($route === '_api/web' || $route === '_api' || $route === '_api/site') {
            return $this->odata($request, $this->web($base), false);
        }

        if (Str::endsWith($route, '.xml')) {
            return $this->manifestResponse();
        }

        if ($route === '') {
            return $this->decorate(response('ChimeIn add-in catalog', 200)
                ->header('Content-Type', 'text/plain'));
        }

        Log::info('office.catalog unhandled path', ['path' => $request->path()]);

        return $this->decorate(response()->json(['error' => 'not found'], 404));
    }

    /** Server-relative root of the catalog, derived from however the client reached us. */
    private function basePath(Request $request, $path) {
        $full = '/' . trim($request->path(), '/');

        if ($path !== '') {
            $full = substr($full, 0, strlen($full) - strlen($path) - 1);
        }

        return rtrim($full, '/');
    }

    private function manifest() {
        return view('office.manifest')->render();
    }

    private function manifestResponse() {
        return $this->decorate(response($this->manifest(), 200)
            ->header('Content-Type', 'application/xml'));
    }

    private function fileUrl($base) {
        return $base . '/' . self::LIBRARY_NAME . '/' . self::MANIFEST_FILE;
    }

    private function listId() {
        return config('office.catalog_list_id');
    }

    private function modified() {
        return '2024-01-01T00:00:00Z';
    }

    private function web($base) {
        return [
            '__metadata' => ['id' => url($base) . '/_api/web', 'uri' => url($base) . '/_api/web', 'type' => 'SP.Web'],
            'Id' => config('office.catalog_web_id'),
            'Title' => 'ChimeIn Add-in Catalog',
            'ServerRelativeUrl' => $base === '' ? '/' : $base,
            'Url' => url($base),
            'Created' => $this->modified(),
            'Language' => 1033,
        ];
    }

    private function currentUser($base) {
        return [
            '__metadata' => ['id' => url($base) . '/_api/web/currentuser', 'uri' => url($base) . '/_api/web/currentuser', 'type' => 'SP.User'],
            'Id' => 1,
            'LoginName' => 'chimein\\catalog',
            'Title' => 'ChimeIn Catalog',
            'IsSiteAdmin' => false,
            'PrincipalType' => 1,
        ];
    }

    private function contextInfo($base) {
        return [
            'GetContextWebInformation' => [
                '__metadata' => ['type' => 'SP.RequestContextInfo'],
                'FormDigestTimeoutSeconds' => 1800,
                'FormDigestValue' => '0x' . strtoupper(bin2hex(random_bytes(16))) . ',' . gmdate('d M Y H:i:s') . ' -0000',
                'LibraryVersion' => '16.0.0.10337',
                'SiteFullUrl' => url($base),
                'WebFullUrl' => url($base),
                'SupportedSchemaVersions' => ['results' => ['14.0.0.0', '15.0.0.0']],
            ],
        ];
    }

    private function list($base) {
        $uri = url($base) . "/_api/web/lists(guid'" . $this->listId() . "')";

        return [
            '__metadata' => ['id' => $uri, 'uri' => $uri, 'type' => 'SP.List'],
            'Id' => $this->listId(),
            'Title' => 'Apps for Office',
            'BaseTemplate' => 101,
            'BaseType' => 1,
            'EntityTypeName' => self::LIBRARY_NAME,
            'ListItemEntityTypeFullName' => 'SP.Data.' . self::LIBRARY_NAME . 'Item',
            'TemplateFeatureId' => self::APPS_FOR_OFFICE_FEATURE_ID,
            'ItemCount' => 1,
            'Hidden' => false,
            'LastItemModifiedDate' => $this->modified(),
            'RootFolder' => [
                '__metadata' => ['type' => 'SP.Folder'],
                'Name' => self::LIBRARY_NAME,
                'ServerRelativeUrl' => $base . '/' . self::LIBRARY_NAME,
                'ItemCount' => 1,
            ],
        ];
    }

    private function file($base) {
        $url = $this->fileUrl($base);
        $uri = url($base) . "/_api/web/GetFileByServerRelativeUrl('" . $url . "')";

        return [
            '__metadata' => ['id' => $uri, 'uri' => $uri, 'type' => 'SP.File'],
            'UniqueId' => config('office.catalog_file_id'),
            'Name' => self::MANIFEST_FILE,
            'Title' => 'ChimeIn Question',
            'ServerRelativeUrl' => $url,
            'LinkingUrl' => url($url),
            'TimeCreated' => $this->modified(),
            'TimeLastModified' => $this->modified(),
            'Length' => (string) strlen($this->manifest()),
            'MajorVersion' => 1,
            'MinorVersion' => 0,
            'Exists' => true,
            'Level' => 1,
        ];
    }

    private function item($base) {
        $uri = url($base) . "/_api/web/lists(guid'" . $this->listId() . "')/items(1)";

        return [
            '__metadata' => ['id' => $uri, 'uri' => $uri, 'etag' => '"1"', 'type' => 'SP.Data.' . self::LIBRARY_NAME . 'Item'],
            'Id' => 1,
            'ID' => 1,
            'GUID' => config('office.catalog_file_id'),
            'Title' => 'ChimeIn Question',
            'FileLeafRef' => self::MANIFEST_FILE,
            'FileRef' => $this->fileUrl($base),
            'FileSystemObjectType' => 0,
            'Modified' => $this->modified(),
            'Created' => $this->modified(),
            'ProductId' => '{' . trim(config('office.content_addin_id'), '{}') . '}',
            'VersionString' => '1.0.0.0',
            'File' => $this->file($base),
        ];
    }

    /**
     * Office negotiates either OData verbose JSON or the older Atom format, so answer in
     * whichever one it asked for.
     */
    private function odata(Request $request, array $data, $collection) {
        $accept = strtolower((string) $request->header('Accept'));

        if (Str::contains($accept, 'atom') && ! Str::contains($accept, 'json')) {
            return $this->decorate(response($this->atom($request, $data, $collection), 200)
                ->header('Content-Type', 'application/atom+xml;type=' . ($collection ? 'feed' : 'entry') . ';charset=utf-8'));
        }

        if (Str::contains($accept, 'odata=verbose') || ! Str::contains($accept, 'odata=')) {
            $body = ['d' => $collection ? ['results' => $data] : $data];
            $type = 'application/json;odata=verbose;charset=utf-8';
        } else {
            $body = $collection ? ['value' => $this->stripMetadata($data)] : $this->stripMetadata($data);
            $type = 'application/json;odata=nometadata;charset=utf-8';
        }

        return $this->decorate(response()->json($body)->header('Content-Type', $type));
    }

    private function stripMetadata(array $data) {
        $clean = [];

        foreach ($data as $key => $value) {
            if ($key === '__metadata') {
                continue;
            }

            $clean[$key] = is_array($value) ? $this->stripMetadata($value) : $value;
        }

        return $clean;
    }

    private function atom(Request $request, array $data, $collection) {
        $entries = $collection ? $data : [$data];
        $xml = '<?xml version="1.0" encoding="utf-8"?>';

        if ($collection) {
            $xml .= '<feed xml:base="' . e(url($request->path())) . '" ' . $this->atomNamespaces() . '>'
                . '<id>' . e(url($request->path())) . '</id><title /><updated>' . $this->modified() . '</updated>';
        }

        foreach ($entries as $entry) {
            $xml .= $this->atomEntry($entry, ! $collection);
        }

        return $xml . ($collection ? '</feed>' : '');
    }

    private function atomNamespaces() {
        return 'xmlns="http://www.w3.org/2005/Atom" '
            . 'xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" '
            . 'xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata"';
    }

    private function atomEntry(array $entry, $standalone) {
        $meta = isset($entry['__metadata']) ? $entry['__metadata'] : [];
        $id = isset($meta['id']) ? $meta['id'] : '';
        $type = isset($meta['type']) ? $meta['type'] : 'SP.ListItem';

        $xml = '<entry' . ($standalone ? ' ' . $this->atomNamespaces() : '') . '>'
            . '<id>' . e($id) . '</id>'
            . '<category term="' . e($type) . '" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme" />'
            . '<title /><updated>' . $this->modified() . '</updated><author><name /></author>';

        $properties = '';

        foreach ($entry as $key => $value) {
            if ($key === '__metadata') {
                continue;
            }

            if (is_array($value)) {
                $xml .= '<link rel="http://schemas.microsoft.com/ado/2007/08/dataservices/related/' . e($key) . '" '
                    . 'type="application/atom+xml;type=entry" title="' . e($key) . '" href="' . e($key) . '">'
                    . '<m:inline>' . $this->atomEntry($value, false) . '</m:inline></link>';
                continue;
            }

            $properties .= $this->atomProperty($key, $value);
        }

        return $xml . '<content type="application/xml"><m:properties>' . $properties . '</m:properties></content></entry>';
    }

    private function atomProperty($key, $value) {
        if (is_bool($value)) {
            return '<d:' . $key . ' m:type="Edm.Boolean">' . ($value ? 'true' : 'false') . '</d:' . $key . '>';
        }

        if (is_int($value)) {
            return '<d:' . $key . ' m:type="Edm.Int32">' . $value . '</d:' . $key . '>';
        }

        $type = '';

        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', (string) $value)) {
            $type = ' m:type="Edm.Guid"';
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}T/', (string) $value)) {
            $type = ' m:type="Edm.DateTime"';
        }

        return '<d:' . $key . $type . '>' . e($value) . '</d:' . $key . '>';
    }

    private function decorate($response) {
        return $response
            ->header('MicrosoftSharePointTeamServices', '16.0.0.10337')
            ->header('X-SharePointHealthScore', '0')
            ->header('SPRequestGuid', (string) Str::uuid())
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}
