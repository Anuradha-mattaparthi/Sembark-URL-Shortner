<?php
//SUPERADMIN DASHBOARD CONTROLLER
namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\ShortUrl;
class DashboardController extends Controller
{

    // fetching all companies clients when we clicking on view more button on Super admin dashboard
    public function index()
    {

        $clients = Company::with(['admin'])
        ->withCount(['users', 'shortUrls'])
        ->withSum('shortUrls', 'hits')
        ->get();

        return view('clients.index', [
               'clients' => $clients,
                'mode' => 'superadmin', // tell the view which layout to show
        ]);
    }

    //fetching all urls created by company when we clicking on view more button on Super admin dashboard
    public function allUrls(Request $request)
    {
        // Load all short urls across companies, with their user and company
        $allUrls = ShortUrl::with(['user:id,name,email', 'company:id,name'])
            ->orderBy('id', 'desc')
            ->get();

        // short domain for building links
        $shortDomain = env('SHORT_DOMAIN', config('app.url'));

        return view('superadminurls_all', compact('allUrls', 'shortDomain'));
    }
}
