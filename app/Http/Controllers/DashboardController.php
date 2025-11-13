<?php

// ==========================================
// app/Http/Controllers/DashboardController.php
// ==========================================
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        return view('dashboard', [
            'user' => $user,
        ]);
    }

    public function adminDashboard()
    {
        return view('admin.dashboard');
    }

    public function agentDashboard()
    {
        return view('agent.dashboard');
    }

    public function clientDashboard()
    {
        return view('client.dashboard');
    }
}