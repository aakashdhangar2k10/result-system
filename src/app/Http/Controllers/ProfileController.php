<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user()->load(['roles', 'groups', 'departments']),
        ]);
    }

    // name and email
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'email.unique' => 'This email is already used by another user.',
        ]);

        // the email is the login: changing it needs the current password
        if (strcasecmp($data['email'], $user->email) !== 0) {
            $request->validate(
                ['current_password' => ['required', 'current_password:web']],
                [
                    'current_password.required' => 'Enter your current password to change the email.',
                    'current_password.current_password' => 'The current password is not correct.',
                ]
            );
        }

        $user->update(['name' => $data['name'], 'email' => $data['email']]);

        return back()->with('success', 'Profile updated.');
    }

    public function password(Request $request)
    {
        $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.required' => 'Enter your current password.',
            'current_password.current_password' => 'The current password is not correct.',
        ]);

        // the password is hashed by the cast on the User model
        $request->user()->update(['password' => $request->password]);

        return back()->with('success', 'Password changed.');
    }

    // upload or replace the photo
    public function photo(Request $request)
    {
        $request->validateWithBag('photo', [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'photo.required' => 'Choose a photo first.',
            'photo.image' => 'The file must be an image.',
            'photo.mimes' => 'The photo must be a .jpg, .jpeg, .png or .webp file.',
            'photo.max' => 'The photo must not be larger than 2 MB.',
        ]);

        $user = $request->user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->update(['photo' => $request->file('photo')->store('profile-photos', 'public')]);

        return back()->with('success', 'Photo updated.');
    }

    public function removePhoto(Request $request)
    {
        $user = $request->user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->update(['photo' => null]);
        }

        return back()->with('success', 'Photo removed. Your initials are shown instead.');
    }
}