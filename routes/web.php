<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShortUrlController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminInviteController;
use App\Models\Company;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MemberDashboardController;
use App\Http\Controllers\AdminInviteController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


 /* SUPERADMIN: Only SuperAdmin can access dashboard*/
 Route::get('/dashboard', function () {

    // Companies list
    $clients = Company::with(['admin'])
        ->withCount(['users'])
        ->withCount(['shortUrls'])
        ->withSum(['shortUrls as short_urls_sum_hits'], 'hits')
        ->get();

    // ALL URLs from all companies (superadmin sees everything)
    $allUrls = ShortUrl::with([
        'user:id,name,email',
        'company:id,name'
    ])
    ->orderBy('id', 'desc')
    ->get();
    $visibleUrls = $allUrls->take(2);
    $hiddenUrls  = $allUrls->slice(2);

    $shortDomain = env('SHORT_DOMAIN', config('app.url'));

    return view('dashboard', compact(
        'clients',
        'visibleUrls',
        'hiddenUrls',
        'shortDomain'
    ));

})->middleware(['auth', 'verified', 'role:superadmin'])->name('dashboard');

Route::middleware(['auth','verified','role:superadmin'])
    ->get('/superadmin/urls/all', [DashboardController::class, 'allUrls'])
    ->name('superadmin.urls.all');


/* SUPERADMIN: Invite Company Admin*/
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::post('/super-admin/invite', [SuperAdminInviteController::class,'send'])
        ->name('super.invite.send');
});


/* INVITATION ACCEPT ROUTES */
Route::get('/invitations/accept/{token}', [SuperAdminInviteController::class,'acceptForm'])
     ->name('invitations.accept');

Route::post('/invitations/accept/{token}', [SuperAdminInviteController::class,'accept'])
     ->name('invitations.accept.post');



/* VIEW ALL CLIENTS (superadmin only) */
Route::get('/clients', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:superadmin'])
    ->name('clients.index');

/* ADMIN and member: Generate Short URLS*/

Route::middleware(['auth'])->group(function () {
    Route::post('/short-urls', [ShortUrlController::class, 'store'])->name('short-urls.store');
});



// Public redirect route — must be outside auth & before any catch-all routes
Route::get('/r/{code}', [ShortUrlController::class, 'redirect'])
    ->name('short-urls.redirect')
    ->where('code', '[A-Za-z0-9]{4,}');



/* ADMIN: Only Admin can access dashboard of 2 clients of admin and members*/
Route::get('/admindashboard', [AdminDashboardController::class, 'dashboard'])
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('admindashboard');

/* ADMIN: view all clients within comapany*/
Route::get('/admin/clients', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('admin.clients.index');



// Admin – fetch all URLs
Route::get('/admin/urls/all', [AdminDashboardController::class, 'allUrls'])
    ->name('admin.urls.all')
    ->middleware(['auth', 'role:admin']);


    //Admin - Invite
Route::middleware(['auth','role:admin'])->group(function () {
    Route::post('/admin/invite', [AdminInviteController::class, 'send'])->name('admin.invite.send');
});

Route::get('/invitations/accept/{token}', [AdminInviteController::class, 'acceptForm'])->name('invitations.accept');
Route::post('/invitations/accept/{token}', [AdminInviteController::class, 'accept'])->name('invitations.accept.post');


    /* Members: members generate urls*/
    Route::get('/memberdashboard', [MemberDashboardController::class, 'dashboard'])
    ->middleware(['auth', 'verified', 'role:member'])
    ->name('memberdashboard');



    Route::get('/logout', function () {
        Auth::logout();
        return redirect('/login');
    });

require __DIR__.'/auth.php';
