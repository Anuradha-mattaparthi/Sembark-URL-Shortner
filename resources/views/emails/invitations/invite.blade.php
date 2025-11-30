{{-- Sending the Invite email from admin dashboard --}}
<x-mail::message>
# Invitation to Join Sembark

Hello **{{ $inv->name }}**,
You have been invited to join **{{ $inv->company->name }}** as an **{{ ucfirst($inv->role) }}**.

Click the button below to accept your invitation:

<x-mail::button :url="$url">
Accept Invitation
</x-mail::button>

If the button above doesn’t work, paste this URL into your browser:

{{ $url }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
