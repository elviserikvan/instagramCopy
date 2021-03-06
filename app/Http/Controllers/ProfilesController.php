<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use App\User;

class ProfilesController extends Controller
{

    /**
    * Shows the profile of a specific user
    *
    * @param string $user
    */
    public function index(User $user)
    {
        $follows = (auth()->user()) ? auth()->user()->following->contains($user->id) : false;

        $postCount = Cache::remember(
            'count.posts.' . $user->id,
            now()->addSeconds(30),
            function() use ($user) {
                return $user->posts->count();
            });

        $followersCount = Cache::remember(
            'count.followers.' . $user->id,
            now()->addSeconds(30),
            function() use ($user) {
                return $user->profile->followers->count();
            });

        $followingCount = Cache::remember(
            'count.following.' . $user->id,
            now()->addSeconds(30),
            function() use ($user) {
                return $user->profile->followers->count();
            });


        return view('profiles.index', compact('user', 'follows', 'postCount', 'followersCount', 'followingCount'));
    }

    /**
    * Edit user's profile
    *
    * @param string $user
    *
    * @return view
    *
    */
    public function edit(User $user)
    {
        $this->authorize('update', $user->profile);
        return view('profiles.edit', compact('user'));
    }


    /**
    * Update user's profile
    *
    * @param string $user
    *
    * @return void
    *
    */
    public function update(User $user)
    {
        $this->authorize('update', $user->profile);
        $data = request()->validate([
            'url' => 'url',
            'image' => '',
            'title' => 'required',
            'description' => 'required'
        ]);


        if (request('image')) {
            $imagePath = request('image')->store('profile', 'public');

            $image = Image::make(public_path("/storage/{$imagePath}"))->fit(1000,1000);
            $image->save();
            $imageArray = ['image' => $imagePath];
        }

        auth()->user()->profile->update(array_merge(
            $data,
            $imageArray ?? []
        ));

        return redirect("/profile/{$user->id}");
    }

}
