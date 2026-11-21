<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Why the API rejected a project token. Sent to the CLI as the `reason` of a
 * 401 response, so that it can tell "replace your token" from "this project
 * is gone".
 */
enum TokenRejection: string
{
    /** Unknown token: mistyped, issued by another server, or never issued. */
    case InvalidToken = 'invalid_token';

    /** The token was replaced by a newer one (regenerated). */
    case TokenRevoked = 'token_revoked';

    /** The project the token belonged to no longer exists. */
    case ProjectDeleted = 'project_deleted';
}
