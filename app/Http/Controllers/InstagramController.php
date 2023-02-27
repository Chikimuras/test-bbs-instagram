<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SebastianBergmann\Type\VoidType;

class InstagramController extends Controller
{

    /**
     * Redirect to Instagram auth page if the user isn't stored in session or if the user access token is expired
     *
     * @return RedirectResponse
     *
     * @throws GuzzleException
     */
    public function redirectToInstagramAuth(): RedirectResponse
    {

        $client_id = env('INSTAGRAM_CLIENT_ID');
        $redirect_uri = env('INSTAGRAM_REDIRECT_URI');
        $scope = 'user_profile,user_media';

        $url = "https://api.instagram.com/oauth/authorize?client_id=$client_id&redirect_uri=$redirect_uri&scope=$scope&response_type=code";

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);

        if ($response->getStatusCode() === 200) {
            return Redirect::to($url);
        }

        return Redirect::to('/');
    }

    /**
     * Get Instagram access token from Instagram code
     *
     * @param Request $request
     *
     *
     * @return RedirectResponse
     * @throws GuzzleException
     */
    public function getInstagramToken(Request $request): RedirectResponse
    {
        $code = $request->input('code');
        $client_id = env('INSTAGRAM_CLIENT_ID');
        $client_secret = env('INSTAGRAM_CLIENT_SECRET');
        $redirect_uri = env('INSTAGRAM_REDIRECT_URI');

        $url = "https://api.instagram.com/oauth/access_token";

        $response = Http::asForm()->post($url, [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirect_uri,
            'code' => $code,
        ]);

        if ($response->failed() && $response->status() === 400) {
            return $this->redirectToInstagramAuth();
        }

        $response = $response->json();

        // Check if the session has the token_id and the user_id, if not, create new entry in session
        if (!session()->has('token_id') && !session()->has('user_id')) {
            session()->put('token_id', $response['access_token']);
            session()->put('user_id', $response['user_id']);

            //Add the token expiration date to the session
            session()->put('token_expires_at', now()->addHour());
        }

        return $this->getInstagramMedia($response['access_token'], $response['user_id']);
    }

    /**
     * Get all media from Instagram account
     *
     * @param $access_token
     * @param $user_id
     * @return RedirectResponse
     * @throws GuzzleException
     */
    public function getInstagramMedia($access_token, $user_id): RedirectResponse
    {

        $url = "https://graph.instagram.com/$user_id/media?fields=id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,username&limit=5&access_token=$access_token";

        $response = Http::get($url);

        if ($response->failed() && $response->status() === 400) {
            return $this->redirectToInstagramAuth();
        }

        $response = $response->json();

        session()->put('instagram_media', $response['data']);

        return redirect()->route('displayMedia');
    }

    /**
     * @throws GuzzleException
     */
    public function getInstagramFeed(): RedirectResponse
    {
       //Check if the session has the token_id and the user_id, if not, redirect to Instagram auth page
        if (!session()->has('token_id') && !session()->has('user_id')) {
            return $this->redirectToInstagramAuth();
        }

        //Check if the token is expired, if yes, redirect to Instagram auth page
        if (session()->get('token_expires_at') < now()) {
            return $this->redirectToInstagramAuth();
        }

        return $this->getInstagramMedia(session()->get('token_id'), session()->get('user_id'));
    }

    /**
     * Display the Instagram media
     *
     * @return RedirectResponse|View
     */
    public function displayMedia(): RedirectResponse|View
    {
        try {
            $media = session()->get('instagram_media');
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            return redirect()->route('getInstagramFeed');
        }

        return view('instagram.feed', ['media' => $media]);
    }
}
