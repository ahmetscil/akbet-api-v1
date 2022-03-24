<?php
use Illuminate\Http\Request;

/*
    şifresiz erişilebilecek standart ekranların tamamı burada
Route::apiResources([
    'user' => 'Api\UserController'
]);
*/


Route::post('/uplink', 'Api\UplinkController@store');
Route::post('/auth/login', 'Api\AuthController@login');
Route::post('/auth/register', 'Api\AuthController@register');

Route::middleware('api-token')->group(function() {
/*
    apiToken middlewarei user tablosunda api_token sütununu sorguluyor.
    şifre kontrolü yapılacak ekranları buraya yerleştiriyoruz.
*/


    Route::apiResource('/company', 'Api\CompanyController');
    Route::apiResource('/project', 'Api\ProjectController');
    Route::apiResource('/mix', 'Api\MixController');
    Route::apiResource('/section', 'Api\SectionController');
    Route::apiResource('/mixcalibration', 'Api\MixCalibrationController');
    Route::apiResource('/devices', 'Api\DevicesController');
    Route::apiResource('/postdata', 'Api\PostDataController');
    Route::apiResource('/user', 'Api\UserController');
    Route::apiResource('/content', 'Api\ContentController');
    Route::apiResource('/authority', 'Api\AuthorityController');
    Route::apiResource('/dashboard', 'Api\DashboardController');
    Route::apiResource('/log', 'Api\LogController');
    Route::post('/upload', 'Api\UploadController@upload');
    Route::post('/storage', 'Api\UploadController@storage');
    Route::post('/s3', 'Api\UploadController@s3');
    Route::post('/removeS3', 'Api\UploadController@removeS3');
    Route::get('/uplink', 'Api\UplinkController@index');
    Route::get('/uplink/{deveui}/{select}', 'Api\UplinkController@show');
});
