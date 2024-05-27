<?php

namespace App\Queries;

use Scaleflex\Commons\Query;

class SqlQuery
{
    public static function getDemo()
    {
        $sql = sprintf("SELECT 1 as d");

        return Query::fetch($sql);
    }

    /**
     * API: GET - api/keys/invalidation-check
     */
    public static function getSessionByUUid($sessionUuid)
    {
        $sql = sprintf("SELECT user_id from s_sessions WHERE session_uuid = %s", Query::qs($sessionUuid));

        return Query::fetch($sql);
    }

    /**
     * API: GET - api/keys/invalidation-check
     */
    public static function getAllPermissionsByUserIdQuery($userId, $projectToken)
    {
        $sql = sprintf("SELECT * FROM (SELECT *,  (SELECT token_value FROM f_all_tokens WHERE filerobot_project_id = x.project_id)
            FROM __get_all_projects_and_companies_related_to_user_id(%s::integer) x
        ) xx
        WHERE xx.token_value = %s", $userId, Query::qs($projectToken));

        return Query::fetchAll($sql);
    }

    /**
     * API: GET - api/keys/invalidation-check
     */
    public static function getKeyPermissionsBySecretKeyQuery($keyUuid, $projectToken)
    {
        $sql = sprintf("SELECT * FROM (SELECT *,  (select token_value from f_all_tokens where filerobot_project_id = x.project_id)
            FROM _get_all_projects_related_to_secret_key(%s::uuid) x
        ) xx
        WHERE token_value = %s", Query::qs($keyUuid), Query::qs($projectToken));

        return Query::fetchAll($sql);
    }

}
