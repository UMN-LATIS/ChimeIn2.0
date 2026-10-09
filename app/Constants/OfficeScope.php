<?php

namespace App\Constants;

use App\Chime;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Token abilities for the PowerPoint add-in.
 *
 * A "browse" token stays on the presenter's machine and only lists the chimes
 * they present. Chime tokens stay in Office client storage; presentations
 * contain selection metadata, not credentials.
 */
class OfficeScope {
    public const BROWSE = 'office:browse';

    public const READ = 'read';
    public const PRESENT = 'present';
    public const RESULTS = 'results';

    public const CHIME_SCOPES = [self::READ, self::PRESENT, self::RESULTS];

    public const BROWSE_TOKEN_NAME = 'office-browse';

    public static function forChime(int $chimeId, string $scope): string {
        return "chime:{$chimeId}:{$scope}";
    }

    /**
     * @return array<int, string>
     */
    public static function allForChime(int $chimeId): array {
        return array_map(
            fn(string $scope) => self::forChime($chimeId, $scope),
            self::CHIME_SCOPES
        );
    }

    public static function tokenNameForChime(Chime $chime): string {
        return "office-chime-{$chime->id}";
    }

    /**
     * The chime a token is bound to, or null for a browse token.
     */
    public static function chimeIdFor(PersonalAccessToken $token): ?int {
        foreach ($token->abilities ?? [] as $ability) {
            if (preg_match('/^chime:(\d+):/', $ability, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return null;
    }
}
