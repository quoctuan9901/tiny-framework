<?php

namespace App\Controllers;
use App\Queries\SqlQuery;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Scaleflex\Commons\ApiResponse;

class LogsController extends BaseController
{
    /**
     * LogsController constructor.
     */
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function invalidationCheck (Request $request) {
        $api_response = [
            'hint' => "This INTERNAL API is meant to check if the info given by user for an invalidation are correct and match the user projects (or not). Basically, it will validate a invalidation procedure. The invalidation should be processed only is status=success.  ",
            'input_available' => "Can be ('filerobot_session_uuid' + 'filerobot_project_token')  or  ('filerobot_key_uuid' + 'filerobot_project_token')",
        ];

        try {
            // Auth via filerobot_session_uuid
            if ($request->has('filerobot_session_uuid')) {
                $api_response['input_chosen'] = "'filerobot_session_uuid' : '{$request->filerobot_session_uuid}'";

                // Get user ID from session
                $session_user = SqlQuery::getSessionByUUid($request->filerobot_session_uuid);

                if (!$session_user) {
                    return ApiResponse::error("This session doesn't look correct... cannot find user_id", $api_response);
                } else {
                    $api_response['input_user_id_found'] = $session_user->user_id;
                }

                // Get permissions for the project
                $session_permissions = SqlQuery::getAllPermissionsByUserIdQuery($session_user->user_id, $request->filerobot_project_token);

                foreach ($session_permissions as $p) {
                    if (in_array($p->level, ['owner', 'co_owner', 'admin', 'developer', 'manager', 'administrator'])) {
                        return ApiResponse::success($api_response, "Flush allowed, user has level {$p->level} on this project");
                    }
                }

                // No matching permission
                return ApiResponse::error("Couldn't find proper permission for this user", $api_response);
            }

            // Auth via filerobot_key_uuid
            if ($request->has('filerobot_key_uuid')) {
                $api_response['input_chosen'] = "'filerobot_key_uuid' : '{$request->filerobot_key_uuid}'";

                // Get permissions for the project
                $session_permissions = SqlQuery::getKeyPermissionsBySecretKeyQuery($request->filerobot_key_uuid, $request->filerobot_project_token);

                foreach ($session_permissions as $p) {
                    // TODO: Check if the secret key can allow FLUSH/INVALIDATION
                    // Currently not checked, but easy to add here once you decide which permission it requires
                    // if (in_array($p->level, ['owner', 'admin', 'developer'])) {
                        return ApiResponse::success($api_response, "Flush allowed, key matches with the project");
                    // }
                }

                // No matching permission
                return ApiResponse::error("Couldn't find proper permission for this user", $api_response);
            }

            // No matching authentication
            return ApiResponse::error("Couldn't find any user with the authentication given", $api_response);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), $api_response);
        }
    }
}
