<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminInviteController extends Controller
{
    // Send invitation to member or admin of company

    public function send(Request $request)
    {
        $inviter = Auth::user();

        if (! $inviter || $inviter->role !== 'admin') {
            abort(403, 'Only company admins can send invitations.');
        }

        $validated = $request->validate([
            'name'  => ['required','string','max:255'],
            'email' => ['required','email','max:255'],
            'role'  => ['required','in:admin,member'],
        ]);

        DB::beginTransaction();

        try {

            if (! $inviter->company_id) {
                throw new Exception('Inviter is not attached to a company.');
            }


            $token = hash_hmac('sha256', Str::random(40), config('app.key'));
            $invite = Invitation::create([
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'role'       => strtolower($validated['role']),
                'company_id' => $inviter->company_id,
                'token'      => $token,
                'status'     => 'pending',
                'sent_by'    => $inviter->id,
                'sent_at'    => now(),
            ]);

            Log::info('Company invitation generated', [
                'url' => route('invitations.accept', $invite->token),
                'company_id' => $inviter->company_id,
            ]);

            Mail::to($invite->email)->send(new InvitationMail($invite));

            DB::commit();

            return back()->with('success', 'Invitation sent to ' . $invite->email);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Admin invite failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to send invitation: ' . $e->getMessage());
        }
    }


// Show accept form
public function acceptForm(string $token)
{
    $invite = Invitation::with('company')->where('token', $token)
        ->where('status', 'pending')
        ->first();

    if (! $invite) {
        Log::warning('Invitation accept: token not found', ['token' => $token]);
        abort(404, 'Invitation not found or already used.');
    }

    if (! $invite->company_id) {
        Log::warning('Invitation missing company_id', ['invite_id' => $invite->id, 'token' => $token]);
        return view('emails.invitations.accept_missing_company', compact('invite'));
    }

    if (! $invite->company) {
        Log::warning('Invitation company not found', [
            'invite_id' => $invite->id,
            'company_id' => $invite->company_id,
            'token' => $token
        ]);
        return view('emails.invitations.accept_missing_company', compact('invite'));
    }

    return view('emails.invitations.accept', compact('invite'));
}

// Process accept form
public function accept(Request $request, string $token)
{
    $invite = Invitation::where('token', $token)
        ->where('status', 'pending')
        ->first();

    if (! $invite) {
        abort(404, 'This invitation link is invalid or expired.');
    }

    // ensure the invitation still has a company
    if (! $invite->company_id || ! $invite->company) {
        return back()->withErrors(['company' => 'The company for this invitation no longer exists.']);
    }

    $data = $request->validate([
        'name' => ['required','string','max:255'],
        'password' => ['required','confirmed', Password::min(8)],
    ]);

    DB::beginTransaction();

    try {
        $user = User::create([
            'name'       => $data['name'],
            'email'      => $invite->email,
            'password'   => Hash::make($data['password']),
            'role'       => strtolower($invite->role),
            'company_id' => $invite->company_id,
        ]);

        $invite->update([
            'status' => 'accepted',
            'accepted_by' => $user->id,
            'accepted_at' => now(),
        ]);

        DB::commit();

        Auth::login($user);

        if ($user->role === 'admin') {
            return redirect()->route('admindashboard')
                ->with('success', 'Invitation accepted!');
        }

        if ($user->role === 'member') {
            return redirect()->route('memberdashboard')
                ->with('success', 'Invitation accepted!');
        }

    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Admin invitation accept failed', ['error' => $e->getMessage(), 'token' => $token]);
        return back()->with('error', 'Failed to accept invitation: ' . $e->getMessage());
    }
}

}


