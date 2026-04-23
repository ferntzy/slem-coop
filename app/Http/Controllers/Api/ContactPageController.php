<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\ContactPageSetting;
use Illuminate\Http\Request;

class ContactPageController extends Controller
{
    /**
     * Return all Contact page settings for the React frontend.
     */
    public function show()
    {
        $s = ContactPageSetting::getSetting();

        return response()->json([
            'hero' => [
                'badge' => $s->hero_badge,
                'title' => $s->hero_title,
                'subtitle' => $s->hero_subtitle,
            ],
            'info' => [
                'phone' => $s->phone,
                'email' => $s->email,
                'address' => $s->address,
                'hours' => $s->hours,
            ],
            'social' => [
                'facebook' => $s->facebook_url,
                'twitter' => $s->twitter_url,
                'instagram' => $s->instagram_url,
                'linkedin' => $s->linkedin_url,
            ],
            'maps_embed_url' => $s->maps_embed_url,
            'branches' => $s->branches ?? [],
        ]);
    }

    /**
     * Store a contact form submission from the React frontend.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        ContactMessage::create($validated);

        return response()->json([
            'message' => "Message sent successfully! We'll get back to you soon.",
        ], 201);
    }
}
