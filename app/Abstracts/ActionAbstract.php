<?php


namespace App\Abstracts;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Cache;
use App\Interfaces\ActionInterface;
use Illuminate\Support\Facades\Http;

abstract class ActionAbstract implements ActionInterface
{
	const METHOD_GET    = 'GET';
	const METHOD_POST   = 'POST';
	const METHOD_PATCH  = 'PATCH';
	const METHOD_DELETE = 'DELETE';
	const METHOD_ANY    = 'ANY';

	protected $should_cache = true; // TODO: for testing. change it later
	protected $cache_key  = 'cache_key';
	protected $caceh_ttl  = 60;

	public function run()
	{
		return 'Not Added!';
	}

	public function render()
	{
		if (!$this->should_cache)
		{
			return $this->runRender();
		} 
		else {
			if (is_null($this->cache_key))
			{
				$className = get_class( $this );
				$className ? $this->setCacheKey(md5($className.'_cache')) : $this->cache_key = 'default_action_cache_'.$this->caceh_ttl;
			}
			return Cache::remember($this->cache_key, $this->caceh_ttl, function () {
				return $this->runRender();
			});
		}
	}

	public function runRender()
	{
		$run = $this->run();
		return $run;
	}

	public function method()
	{
		return self::METHOD_GET;
	}

	public function getParameter( $name, $default=null)
	{
		$request = Request::capture();
		return $request->input($name, $default);
	}

	public function validation()
	{
		return [];
	}

	public function getManner()
	{
		return [];
	}


	public function setCacheKey( string $key ): string
	{
		return $this->cache_key = $key;
	}

	public function setCacheTtl( int $ttl ): int
	{
		return $this->cache_ttl = $ttl;
	}

	public function getUserId()
    {
		$request = Request::capture();
		$url = env('AUTH_URL').'/check';
        $token = $request->header('Authorization');
        $response = Http::withHeaders(
            [
                'Authorization' => $token
            ]
        )->get($url);
        $code     = $response->status();
		if ($code == 200) {
			$user =  Cache::remember('user_id_info_'.$token, 120, function() use ($response) {
				$res = json_decode($response);
				return $res->data;
			});
			return $user ?? null;
		}
    }

	public function getUser()
    {
		$request = Request::capture();
		$url = env('AUTH_URL').'/check';
        $token = $request->header('Authorization');
        $response = Http::withHeaders(
            [
                'Authorization' => $token
            ]
        )->get($url);
        $code     = $response->status();
		if ($code == 200) {
			$user =  Cache::remember('user_info_'.$token, 120, function() use ($response) {
				$res = json_decode($response);
				return $res->data;
			});
			return $user ?? null;
		}
    }

}
