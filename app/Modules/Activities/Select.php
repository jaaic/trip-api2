<?php

require "../../../vendor/autoload.php";

use App\Modules\Activities\Request\ActivityRequest;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerException;

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    $exception = new BadRequestException('Forbidden', $_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', '403');
    echo json_encode($exception->toArray());
    exit();
}

// read input
$queryParams = [];
parse_str($_SERVER['QUERY_STRING'], $queryParams);

try {
    $request  = new ActivityRequest($queryParams);
    $response = $request->validate()
                        ->load()
                        ->process();

    $response = json_encode($response);
} catch (BadRequestException | ServerException $exception) {
    $response = json_encode($exception->toArray());
} catch (Exception $exception) {
    $response = [$exception->getMessage(), $exception->getTrace()];
    $response = json_encode($response);
}

echo $response;