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

class SuperAdminInviteController extends Controller
{
    /**
     * SEND INVITATION (SuperAdmin Only)
     */
    public function send(Request $request)
    {
        // Role check (custom roles)
        $user = Auth::user();
        if (! $user || $user->role !== 'superadmin') {
            abort(403, 'Only SuperAdmin can send invitations.');
        }

        // Validate input
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255'],
        ]);

        DB::beginTransaction();

        try {
            // Create the Company
            $company = Company::create([
                'name'       => $validated['company_name'],
                'created_by' => $user->id,
            ]);

            // Generate token
            $token = hash_hmac('sha256', Str::random(40), config('app.key'));

            // Store invitation
            $invite = Invitation::create([
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'role'       => 'admin',
                'company_id' => $company->id,
                'token'      => $token,
                'status'     => 'pending',
                'sent_by'    => $user->id,
                'sent_at'    => now(),
            ]);

            // Log URL for debugging
            Log::info('Invitation URL generated', [
                'url' => route('invitations.accept', $invite->token),
            ]);

            // Send Email
            Mail::to($invite->email)->send(new InvitationMail($invite));

            DB::commit();

            return back()->with('success', 'Invitation sent successfully to ' . $invite->email);


        } catch (Exception $e) {

            DB::rollBack();
            Log::error('Invitation creation/sending failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Failed to send invitation: ' . $e->getMessage());
        }
    }

    /**
     * SHOW INVITATION ACCEPT FORM
     */
    public function acceptForm(string $token)
    {
        $invite = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (! $invite) {
            abort(404, 'Invitation not found or already used.');
        }

        return view('emails.invitations.accept', compact('invite'));

    }

    /**
     * PROCESS INVITATION ACCEPT
     */
    public function accept(Request $request, string $token)
    {
        $invite = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (! $invite) {
            abort(404, 'This invitation link is invalid or expired.');
        }

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'password' => ['required','confirmed', Password::min(8)],
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $invite->email,
                'password' => Hash::make($data['password']),
                'role'     => strtolower($invite->role),
                'company_id' => $invite->company_id,
            ]);

            // Attach user to company if needed
            if (isset($user->company_id)) {
                $user->company_id = $invite->company_id;
                $user->save();
            }

            // Mark invitation accepted
            $invite->update([
                'status' => 'accepted',
                'accepted_by' => $user->id,
                'accepted_at' => now(),
            ]);

            DB::commit();

            Auth::login($user);

            return redirect()->route('admindashboard')->with('success', 'Invitation accepted!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Invitation accept failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to accept invitation: ' . $e->getMessage());
        }
    }
}
