<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminOrderStatusController;
use App\Http\Controllers\AdminPageController;
use App\Http\Controllers\AdminPaymentMethodController;
use App\Http\Controllers\AdminPostCatController;
use App\Http\Controllers\AdminPostController;
use App\Http\Controllers\AdminProductCatController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminSlideController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\NewPasswordController as NewPasswordController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/register', [AuthController::class, 'register']);

Route::post('/forgot-password', [NewPasswordController::class, 'forgotPassword']);
Route::post('/reset-password', [NewPasswordController::class, 'reset'])->name('password.reset');

Route::middleware(['auth:sanctum', 'verified', 'authorization'])->group(function () {
    //AdminUserController
    Route::get('/getAllListUsers', [AdminUserController::class, 'getAllListUsers']);
    Route::post('/addUser', [AdminUserController::class, 'addUser']);
    Route::get('/getDetailUserById/{id}', [AdminUserController::class, 'getDetailUserById']);
    Route::put('/editUser', [AdminUserController::class, 'editUser']);
    Route::delete('/softDeleteUser/{id}', [AdminUserController::class, 'softDeleteUser']);
    Route::delete('/forceDelete/{id}', [AdminUserController::class, 'forceDelete']);
    Route::get('/restoreUser/{id}', [AdminUserController::class, 'restoreUser']);
    Route::get('/findUserByKeyword', [AdminUserController::class, 'findUserByKeyword']);

    //AdminRoleController
    Route::get('/getAllListRoles', [AdminRoleController::class, 'getAllListRoles']);

    //AdminProductCatController
    Route::get('/getAllListProductCats', [AdminProductCatController::class, 'getAllListProductCats']);
    Route::get('/getAllListActiveProductCats', [AdminProductCatController::class, 'getAllListActiveProductCats']);
    Route::post('/addProductCat', [AdminProductCatController::class, 'addProductCat']);
    Route::get('/getDetailProductCatById/{id}', [AdminProductCatController::class, 'getDetailProductCatById']);
    Route::put('/editProductCat', [AdminProductCatController::class, 'editProductCat']);
    Route::delete('/deleteProductCat/{id}', [AdminProductCatController::class, 'deleteProductCat']);

    //AdminProductController
    Route::get('/getAllFile', [AdminProductController::class, 'getALlFile']);
    Route::get('/getDetailProduct/{id}', [AdminProductController::class, 'getDetailProduct']);
    Route::post('/addProduct', [AdminProductController::class, 'addProduct']);
    Route::get('/getAllProducts', [AdminProductController::class, 'getAllProducts']);
    Route::delete('/softDeleteProduct/{id}', [AdminProductController::class, 'softDeleteProduct']);
    Route::delete('/forceDeleteProduct/{id}', [AdminProductController::class, 'forceDeleteProduct']);
    Route::get('/restoreProduct/{id}', [AdminProductController::class, 'restoreProduct']);
    Route::post("/editProduct", [AdminProductController::class, 'editProduct']);

    //AdminPostCatController
    Route::get('/getAllListPostCat', [AdminPostCatController::class, 'getAllListPostCat']);
    Route::post('/addPostCat', [AdminPostCatController::class, 'addPostCat']);
    Route::get('/getDetailPostCatById/{id}', [AdminPostCatController::class, 'getDetailPostCatById']);
    Route::put('/editPostCat', [AdminPostCatController::class, 'editPostCat']);
    Route::delete('/deletePostCat/{id}', [AdminPostCatController::class, 'deletePostCat']);
    Route::get('/getAllListActivePostCat', [AdminPostCatController::class, 'getAllListActivePostCat']);

    //AdminPostController
    Route::get('/getAllPosts', [AdminPostController::class, 'getAllPosts']);
    Route::post('/addPost', [AdminPostController::class, 'addPost']);
    Route::get('/getDetailPost/{id}', [AdminPostController::class, 'getDetailPost']);
    Route::post('/editPost', [AdminPostController::class, 'editPost']);
    Route::delete('/softDeletePost/{id}', [AdminPostController::class, 'softDeletePost']);
    Route::get('/restorePost/{id}', [AdminPostController::class, 'restorePost']);
    Route::delete('/forceDeletePost/{id}', [AdminPostController::class, 'forceDeletePost']);

    //AdminOrderStatusController
    Route::get('/getAllOrderStatus', [AdminOrderStatusController::class, 'getAllOrderStatus']);

    //AdminPaymentMethodsController
    Route::get('/getAllPaymentMethods', [AdminPaymentMethodController::class, 'getAllPaymentMethods']);

    //AdminOrderController
    Route::get('/getAllOrders', [AdminOrderController::class, 'getAllOrders']);
    Route::get('/getDetailOrder/{id}', [AdminOrderController::class, 'getDetailOrder']);
    Route::put('/editOrder', [AdminOrderController::class, 'editOrder']);

    //AdminSlideController
    Route::post('/addSlide', [AdminSlideController::class, 'addSlide']);
    Route::get('/getAllSlides', [AdminSlideController::class,'getAllSlides']);
    Route::delete('/softDeleteSlide/{id}', [AdminSlideController::class, 'softDeleteSlide']);
    Route::delete('/forceDeleteSlide/{id}', [AdminSlideController::class, 'forceDeleteSlide']);
    Route::put('/restoreSlide/{id}', [AdminSlideController::class, 'restoreSlide']);
    Route::put('/changeStatusSlide/{id}', [AdminSlideController::class, 'changeStatusSlide']);

    //AdminPageController
    Route::post('/addPage', [AdminPageController::class, 'addPage']);
    Route::get('/getDetailPage/{id}', [AdminPageController::class, 'getDetailPage']);
    Route::put('/editPage', [AdminPageController::class, 'editPage']);
    Route::get('/getAllListPages', [AdminPageController::class, 'getAllListPages']);
    Route::delete('/softDeletePage/{id}', [AdminPageController::class, 'softDeletePage']);
    Route::delete('/forceDeletePage/{id}', [AdminPageController::class, 'forceDeletePage']);
    Route::put('/restorePage/{id}', [AdminPageController::class, 'restorePage']);

    //AdminDashboardController
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/email/verify', [EmailVerificationController::class, 'sendVerificationEmail'])->middleware('auth:sanctum');
Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify')->middleware('auth:sanctum');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');
