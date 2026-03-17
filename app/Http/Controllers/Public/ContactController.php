<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormSubmitted;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'nullable|string|max:255',
            'subject' => 'required|string|in:general,demo,enterprise,support,partnership',
            'message' => 'required|string|max:5000',
        ]);

        // Queue send to support mailbox
        Mail::to(config('mail.support_address', 'support@taxmaster.ng'))
            ->queue(new ContactFormSubmitted($data));

        return redirect()->route('contact')->with('success', 'Your message was sent successfully. We will respond shortly.');
    }
}
