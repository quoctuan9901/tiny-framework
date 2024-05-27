<?php 

namespace App\Controllers;
use App\Queries\SqlQuery;
use ApiResponse;

class ExampleController extends BaseController {

    public function exampleDemoAction ($param, $paramDefault = null) {
        try {
            $demo_data = SqlQuery::getDemo();

            $result = [
                'data' => 'This is ExampleController - exampleDemoAction',
                'parameter: ' => $param . " | " . $paramDefault,
                'demo_result' => $demo_data
            ] ;

            return ApiResponse::success($result, "Demo API");
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
}
?>